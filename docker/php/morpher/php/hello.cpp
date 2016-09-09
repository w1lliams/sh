#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_hello.h"
#include "bsdua/utf8.h"
#include <wchar.h>
#include <memory>
#include <stdexcept>
#include <stdio.h>

#if PHP_MAJOR_VERSION < 7
#define _RETURN_STRING(str) RETURN_STRING(str, 1)
#else
#define _RETURN_STRING(str) RETURN_STRING(str)
#endif

#include "../russian/morpher-api-cpp.h"
#include "../russian/RussianCurrencySpeller.h"
#include "../ukrainian/UkrMorpher.h"

using namespace std;

static zend_function_entry hello_functions[] = {
    PHP_FE(morpher_inflect, NULL)
    PHP_FE(morpher_get_gender, NULL)
    PHP_FE(morpher_spell, NULL)
    PHP_FE(morpher_ukr_inflect, NULL)
    PHP_FE(morpher_ukr_get_gender, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry hello_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_HELLO_WORLD_EXTNAME,
    hello_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
#if ZEND_MODULE_API_NO >= 20010901
    PHP_HELLO_WORLD_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_HELLO
ZEND_GET_MODULE(hello)
#endif

using namespace Morpher::Russian;

static const Declension morpher;

typedef const std::tstring (Paradigm::*ParseMemFun) () const;

static bool compare_strings (const char * lengthDefinedString, size_t lengthDefinedStringLength, const char * nullTerminatedString)
{
	bool areEqual = strncmp (lengthDefinedString, nullTerminatedString, lengthDefinedStringLength) == 0;

	bool match = strlen (nullTerminatedString) == lengthDefinedStringLength;

	return areEqual && match;
}

static void print_codes (const wstring & s)
{
	for (wstring::const_iterator i = s.begin (); i != s.end (); ++i)
	{
		printf ("%d ", *i);
	}

	puts ("");
}

static string ToUTF8 (const wstring & result)
{
	int utf8_result_buffer_size = result.length() * 6 + 1; // UTF-8 can take up to 6 bytes per character

	auto_ptr <char> utf8_result (new char [utf8_result_buffer_size]);

	size_t size = wchar_to_utf8 (result.c_str(), result.length(), utf8_result.get(), result.length() * 6, UTF8_SKIP_BOM | UTF8_IGNORE_ERROR);

	utf8_result.get() [size] = '\0';

	return utf8_result.get();
}

static wstring FromUTF8 (const char * utf8_name, size_t utf8_name_size_in_bytes)
{
	auto_ptr <wchar_t> wcsName (new wchar_t [utf8_name_size_in_bytes + 1]);

	size_t wslen = utf8_to_wchar (utf8_name, utf8_name_size_in_bytes, wcsName.get(), utf8_name_size_in_bytes, UTF8_SKIP_BOM | UTF8_IGNORE_ERROR);

	if (wslen == 0)
	{
		throw runtime_error ("utf8_to_wchar failed.");
	}

	wcsName.get() [wslen] = L'\0';

	return wcsName.get();
}

class StringConverter
{
public:
	virtual const wstring GetWStringFromPhrase (const Parse & phrase) = 0;

	const string Utf8ConvertAnalyseConvertBack (
		const char * utf8_name, 
		size_t utf8_name_size_in_bytes)
	{
		if (utf8_name_size_in_bytes == 0)
		{
			return "#ERROR: Parameter 1 'text' should not be empty.";
		}

		wstring wcsName = FromUTF8 (utf8_name, utf8_name_size_in_bytes);

		GC::Ptr <Parse> phrase = morpher.parse (wcsName);

		if (!phrase)
		{
			return "#ERROR: Parameter 1 'text' is not Russian.";
		}

		wstring result = GetWStringFromPhrase (*phrase);

		return ToUTF8 (result);
	}
};

template <typename MF>
class GetCase : public StringConverter
{
	MF memFun;

public:
	GetCase (MF memFun) : memFun (memFun) {}

	virtual const wstring GetWStringFromPhrase (const Parse & phrase)
	{
		return memFun (phrase);
	}

};

template <typename F>
static GetCase <F> getGetCase (F f)
{
	return GetCase <F> (f);
}

class GetGender : public StringConverter
{
public:
	virtual const wstring GetWStringFromPhrase (const Parse & phrase)
	{
		auto rc = phrase.gender ();

		switch (rc)
		{
			case MASCULINE: return L"m";
			case FEMININE:  return L"f";
			case NEUTER:    return L"n";
			case PLURAL:    return L"p";
		}

		return L"#ERROR: Invalid gender.";
	}
};

PHP_FUNCTION(morpher_get_gender)
{
    char *utf8_name;
    size_t utf8_name_size_in_bytes = 0;

	char params [] = "s";

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, params, 
		&utf8_name, 
		&utf8_name_size_in_bytes) == FAILURE) 
	{
        RETURN_NULL(); // PHP will report a warning.
    }

	string utf8_result = GetGender ().Utf8ConvertAnalyseConvertBack(
		utf8_name, 
		utf8_name_size_in_bytes);
		
    _RETURN_STRING((char *)utf8_result.c_str());
}

static UkrMorpher::Morpher ukrMorpher;

PHP_FUNCTION(morpher_ukr_get_gender)
{
    char *utf8_name;
    size_t utf8_name_size_in_bytes = 0;

	char params [] = "s";

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, params, 
		&utf8_name, 
		&utf8_name_size_in_bytes) == FAILURE) 
	{
        RETURN_NULL(); // PHP will report a warning.
    }

	if (utf8_name_size_in_bytes == 0)
	{
		_RETURN_STRING("#ERROR: Parameter 1 'text' should not be empty.");
	}

	wstring name = FromUTF8 (utf8_name, utf8_name_size_in_bytes);

	auto parse = ukrMorpher.Parse (name, UkrMorpher::Gender_Auto);

	_RETURN_STRING(parse.IsMasculine() ? "m" : "f")
}

static ParseMemFun ParsePadeg (const string & padeg)
{
	if (padeg.find ("im") != string::npos)      return &Parse::nominative; 
	if (padeg.find ("rod") != string::npos)     return &Parse::genitive; 
	if (padeg.find ("dat") != string::npos)     return &Parse::dative; 
	if (padeg.find ("vin") != string::npos)     return &Parse::accusative; 
	if (padeg.find ("tvor") != string::npos)    return &Parse::instrumental; 
	if (padeg.find ("predl-o") != string::npos) return &Parse::prepositionalO; 
	if (padeg.find ("predl") != string::npos)   return &Parse::prepositional; 
	if (padeg.find ("gde") != string::npos)     return &Parse::locative; 
	if (padeg.find ("И") != string::npos)       return &Parse::nominative; 
	if (padeg.find ("Р") != string::npos)       return &Parse::genitive; 
	if (padeg.find ("Д") != string::npos)       return &Parse::dative; 
	if (padeg.find ("В") != string::npos)       return &Parse::accusative; 
	if (padeg.find ("Т") != string::npos)       return &Parse::instrumental; 
	if (padeg.find ("П_о") != string::npos)     return &Parse::prepositionalO; 
	if (padeg.find ("П") != string::npos)       return &Parse::prepositional; 
	if (padeg.find ("М") != string::npos)       return &Parse::locative;

	return 0;
}

static int UkrParsePadegInternal (const char ** vidminki, const string & padeg)
{
	for (int i=0; i < 7; ++i)
	{
		if (padeg == vidminki[i]) return i;
	}

	return -1;
}

static int UkrParsePadeg (const string & padeg)
{
	static const char * vidminkiLat [] =
	{
		"naz", "rod", "dav", "zna", "oru", "mis", "kly"
	};

	static const char * vidminkiUkr [] =
	{
		"Н", "Р", "Д", "З", "О", "М", "К"
	};

	int iLat = UkrParsePadegInternal (vidminkiLat, padeg);
	if (iLat != -1) return iLat;

	int iUkr = UkrParsePadegInternal (vidminkiUkr, padeg);
	if (iUkr != -1) return iUkr;

	return -1;
}

PHP_FUNCTION(morpher_inflect)
{
    const char *utf8_name;
    size_t utf8_name_size_in_bytes = 0;
    const char *utf8_padeg;
    size_t utf8_padeg_size_in_bytes = 0;

	char params [] = "ss";

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, params, 
		&utf8_name, 
		&utf8_name_size_in_bytes, 
		&utf8_padeg, 
		&utf8_padeg_size_in_bytes) == FAILURE) 
	{
        RETURN_NULL(); // PHP will report a warning.
    }

	string utf8_result;

	if (utf8_padeg_size_in_bytes == 0)
	{
		utf8_result = "#ERROR: Parameter 2 'case' should not be empty.";
	}
	else
	{
		string padeg (utf8_padeg, utf8_padeg_size_in_bytes);

		bool mn = padeg.find ("mn") != string::npos;

		ParseMemFun padegMF = ParsePadeg (padeg);

		auto converter = getGetCase (
			[padegMF, mn] (const Parse & parse) -> wstring
			{
				const Paradigm * p = &parse;

				if (mn)
				{
					auto plural = parse.plural ();

					if (!plural)
					{
						return L"#ERROR: Parameter 1 'text' is plural.";
					}

					p = plural;
				}

				return (p->*padegMF)();
			}
		);

		utf8_result = !padegMF 
			? "#ERROR: Parameter 2 'case' is invalid."
			: converter.Utf8ConvertAnalyseConvertBack(
				utf8_name, 
				utf8_name_size_in_bytes);
	}
		
    _RETURN_STRING((char *)utf8_result.c_str());
}

static Padeg GetPadegI (ParseMemFun mf)
{
	if (mf == &Parse::nominative) 
		return Padeg_I;
	if (mf == &Parse::genitive) 
		return Padeg_R;
	if (mf == &Parse::dative) 
		return Padeg_D;
	if (mf == &Parse::accusative)
		return Padeg_V;
	if (mf == &Parse::instrumental) 
		return Padeg_T;
	if (mf == &Parse::prepositional) 
		return Padeg_P;
	if (mf == &Parse::prepositionalO || mf == &Parse::locative) 
		return Padeg_M;
	throw runtime_error ("Invalid PadegMF.");
}

PHP_FUNCTION(morpher_spell)
{
    const char *utf8_number;
    size_t utf8_number_size_in_bytes = 0;
    const char *utf8_unit;
    size_t utf8_unit_size_in_bytes = 0;
    const char *utf8_padeg = "И";
    size_t utf8_padeg_size_in_bytes = 2;

	char params [] = "ss|s";

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, params, 
		&utf8_number, 
		&utf8_number_size_in_bytes, 
		&utf8_unit, 
		&utf8_unit_size_in_bytes, 
		&utf8_padeg, 
		&utf8_padeg_size_in_bytes) == FAILURE) 
	{
        RETURN_NULL(); // PHP will report a warning.
    }

	if (utf8_number_size_in_bytes == 0)
	{
		_RETURN_STRING("#ERROR: Parameter 1 'number' should not be empty.");
	}

	if (utf8_unit_size_in_bytes == 0)
	{
		_RETURN_STRING("#ERROR: Parameter 2 'unit' should not be empty.");
	}

	if (utf8_padeg_size_in_bytes == 0)
	{
		_RETURN_STRING("#ERROR: Parameter 3 'case' should not be empty.");
	}

	wstring number = FromUTF8 (utf8_number, utf8_number_size_in_bytes);
	wstring unit = FromUTF8 (utf8_unit, utf8_unit_size_in_bytes);
	string padeg (utf8_padeg, utf8_padeg_size_in_bytes);

	auto padegMF = ParsePadeg (padeg);

	if (padegMF == nullptr)
	{
		_RETURN_STRING("#ERROR: Parameter 3 'case' is invalid.");
	}

	Padeg padeg_i = GetPadegI (padegMF);

	if (padeg_i == Padeg_M)
	{
		_RETURN_STRING("#ERROR: Parameter 3 'case' is invalid.");
	}

	RussianCurrencySpeller speller (morpher.get_declensor ());

	CurrencySpellResult result = speller.Spell (number, unit, (int) padeg_i);

	switch (result.errorCode)
	{
		case CurrencySpellErrorCode_Success:
			break;
		case CurrencySpellErrorCode_NumberIsEmpty:
			throw runtime_error ("Error code CurrencySpellErrorCode_NumberIsEmpty returned."); // We shoudn't get this as we have checked for empty number above.
		case CurrencySpellErrorCode_NumberIsNotANumber:
			_RETURN_STRING("#ERROR: Parameter 1 'number' is not a number.");
		case CurrencySpellErrorCode_NumberIsTooBig:
			_RETURN_STRING("#ERROR: Parameter 1 'number' is too big.");
		case CurrencySpellErrorCode_NumberIsTooSmall:
			_RETURN_STRING("#ERROR: Parameter 1 'number' has more than 30 fraction digits.");
		case CurrencySpellErrorCode_DeclensionError:
			_RETURN_STRING("#ERROR: Parameter 2 'unit' is not Russian or is missing the required form.");
		case CurrencySpellErrorCode_RoundingNeeded:
			_RETURN_STRING("#ERROR: Parameter 1 'number' needs rounding off to two decimal places.");
		default:
			throw runtime_error ("Unexpected CurrencySpellErrorCode.");
	}

	wstring errorOrResult = (result.errorCode == CurrencySpellErrorCode_Success)
		? result.result
		: wstring (L"#ERROR: ") + CurrencySpellErrorCodeToRussianTextMessage (result.errorCode);

	string utf8_result = ToUTF8 (errorOrResult);

    _RETURN_STRING((char *)utf8_result.c_str());
}

PHP_FUNCTION(morpher_ukr_inflect)
{
    const char *utf8_name;
    size_t utf8_name_size_in_bytes = 0;
    const char *utf8_padeg;
    size_t utf8_padeg_size_in_bytes = 0;

	char params [] = "ss";

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, params, 
		&utf8_name, 
		&utf8_name_size_in_bytes, 
		&utf8_padeg, 
		&utf8_padeg_size_in_bytes) == FAILURE) 
	{
        RETURN_NULL(); // PHP will report a warning.
    }

	if (utf8_name_size_in_bytes == 0)
	{
		_RETURN_STRING("#ERROR: Parameter 1 'text' should not be empty.");
	}

	if (utf8_padeg_size_in_bytes == 0)
	{
		_RETURN_STRING("#ERROR: Parameter 2 'case' should not be empty.");
	}

	string padeg (utf8_padeg, utf8_padeg_size_in_bytes);

	int padegI = UkrParsePadeg (padeg);

	if (padegI == -1)
	{
		_RETURN_STRING("#ERROR: Parameter 2 'case' is invalid.");
	}

	wstring name = FromUTF8 (utf8_name, utf8_name_size_in_bytes);

	auto phrase = ukrMorpher.Parse (name, UkrMorpher::Gender_Auto);

	wstring result = phrase.GetFormByCase (padegI);

	string utf8_result = ToUTF8 (result);
		
    _RETURN_STRING((char *)utf8_result.c_str());
}

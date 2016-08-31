#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_hello.h"
#include "bsdua/utf8.h"
#include <wchar.h>
#include <memory>
#include <stdio.h>

#if PHP_MAJOR_VERSION < 7
#define _RETURN_STRING(str) RETURN_STRING(str, 1)
#else
#define _RETURN_STRING(str) RETURN_STRING(str)
#endif

#include "../russian/morpher-api-cpp.h"

using namespace std;

static zend_function_entry hello_functions[] = {
    PHP_FE(morpher_inflect, NULL)
    PHP_FE(morpher_get_gender, NULL)
    {NULL, NULL, NULL}
};

char buildID [] = ZEND_MODULE_BUILD_ID;

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
    NO_MODULE_GLOBALS, 
	NULL, 
	0, 
	0,
	NULL,
	0,
	buildID
};

#ifdef COMPILE_DL_HELLO
ZEND_GET_MODULE(hello)
#endif

using namespace Morpher::Russian;

static const Declension morpher;

typedef const std::tstring (Parse::*ParseMemFun) () const;

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

class StringConverter
{
public:
	virtual const wstring GetWStringFromPhrase (GC::Ptr <Parse> phrase) = 0;

	const string Utf8ConvertAnalyseConvertBack (
		const char * utf8_name, 
		size_t utf8_name_size_in_bytes)
	{
		if (utf8_name_size_in_bytes == 0)
		{
			return "#ERROR: Parameter 1 'text' should not be empty.";
		}

		auto_ptr <wchar_t> wcsName (new wchar_t [utf8_name_size_in_bytes + 1]);

		size_t wslen = utf8_to_wchar (utf8_name, utf8_name_size_in_bytes, wcsName.get(), utf8_name_size_in_bytes, UTF8_SKIP_BOM | UTF8_IGNORE_ERROR);

		if (wslen == 0)
		{
			return "#ERROR: utf8_to_wchar.";
		}

		wcsName.get() [wslen] = L'\0';

		GC::Ptr <Parse> phrase = morpher.parse (wcsName.get());

		if (!phrase)
		{
			return "#ERROR: Parameter 1 'text' is not Russian.";
		}

		wstring result = GetWStringFromPhrase (phrase);

		int utf8_result_buffer_size = result.length() * 6 + 1; // UTF-8 can take up to 6 bytes per character

		auto_ptr <char> utf8_result (new char [utf8_result_buffer_size]);

		size_t size = wchar_to_utf8 (result.c_str(), result.length(), utf8_result.get(), result.length() * 6, UTF8_SKIP_BOM | UTF8_IGNORE_ERROR);

		utf8_result.get() [size] = '\0';

		return utf8_result.get();
	}
};

class GetCase : public StringConverter
{
	ParseMemFun memFun;

public:
	GetCase (ParseMemFun memFun) : memFun (memFun) {}

	virtual const wstring GetWStringFromPhrase (GC::Ptr <Parse> phrase)
	{
		return (phrase.GetP ()->*memFun) ();
	}
};

class GetGender : public StringConverter
{
public:
	virtual const wstring GetWStringFromPhrase (GC::Ptr <Parse> phrase)
	{
		Gender rc = phrase->gender ();

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

PHP_FUNCTION(morpher_inflect)
{
    char *utf8_name;
    size_t utf8_name_size_in_bytes = 0;
    char *utf8_padeg;
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
		ParseMemFun padegMF = 0;

		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "rod"))     padegMF = &Parse::genitive; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "dat"))     padegMF = &Parse::dative; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "vin"))     padegMF = &Parse::accusative; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "tvor"))    padegMF = &Parse::instrumental; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "predl"))   padegMF = &Parse::prepositional; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "predl-o")) padegMF = &Parse::prepositionalO; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "gde"))     padegMF = &Parse::locative; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "Р"))       padegMF = &Parse::genitive; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "Д"))       padegMF = &Parse::dative; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "В"))       padegMF = &Parse::accusative; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "Т"))       padegMF = &Parse::instrumental; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "П"))       padegMF = &Parse::prepositional; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "П_о"))     padegMF = &Parse::prepositionalO; else
		if (compare_strings (utf8_padeg, utf8_padeg_size_in_bytes, "М"))       padegMF = &Parse::locative; else
		{
			utf8_result = "#ERROR: Parameter 2 'case' is invalid.";
		}

		if (padegMF)
		{
			utf8_result = GetCase (padegMF).Utf8ConvertAnalyseConvertBack(
				utf8_name, 
				utf8_name_size_in_bytes);
		}
	}

		
    _RETURN_STRING((char *)utf8_result.c_str());
}

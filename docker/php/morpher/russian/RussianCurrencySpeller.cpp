#include "RussianCurrencySpeller.h"
#include <limits>
#include <map>
#include <iomanip>

using namespace std;

struct Currency
{
	tstring name;
	tstring cents;
};

typedef map <tstring, Currency> Currencies;

static Currencies BuildCurrencies ()
{
	static Currency rub = {_T("российский рубль"), _T("копейка")};
	static Currency usd = {_T("доллар США"), _T("цент")};
	static Currency usd_short = {_T("долл США"), _T("цент")};
	static Currency usd_short_dot = {_T("долл. США"), _T("цент")};
	static Currency eur = {_T("евро"), _T("цент")};
	static Currency kzt = {_T("казахский тенге"), _T("тиын")};
	static Currency byr = {_T("белорусский рубль"), _T("")};
	static Currency uah = {_T("украинская гривна"), _T("копейка")};

	static Currency ruble = {_T("рубль"), _T("копейка")};
	static Currency ruble_short = {_T("руб"), _T("коп")};
	static Currency ruble_short_dot = {_T("руб."), _T("коп.")};
	static Currency dollar = {_T("доллар США"), _T("цент")};
	static Currency tenge = {_T("тенге"), _T("тиын")};
	static Currency tng = {_T("тнг"), _T("тиын")};
	static Currency tng_dot = {_T("тнг."), _T("тиын")};
	static Currency grivna = {_T("гривна"), _T("копейка")};
	static Currency grn = {_T("грн"), _T("коп")};
	static Currency grn_dot = {_T("грн."), _T("коп.")};

	Currencies ccys;

	ccys [_T("RUB")] = rub;
	ccys [_T("RUR")] = rub;
	ccys [_T("руб")] = ruble_short;
	ccys [_T("руб.")] = ruble_short_dot;
	ccys [_T("рубль")] = ruble;
	ccys [_T("рубли")] = ruble;
	ccys [_T("российский рубль")] = rub;
	ccys [_T("российские рубли")] = rub;

	ccys [_T("USD")] = usd;
	ccys [_T("долл")] = usd_short;
	ccys [_T("долл.")] = usd_short_dot;
	ccys [_T("доллар")] = dollar;
	ccys [_T("доллары")] = dollar;
	ccys [_T("долл США")] = usd_short;
	ccys [_T("долл. США")] = usd_short_dot;
	ccys [_T("доллар США")] = usd;
	ccys [_T("доллары США")] = usd;

	ccys [_T("EUR")] = eur;
	ccys [_T("евро")] = eur;

	ccys [_T("KZT")] = kzt;
	ccys [_T("тнг")] = tng;
	ccys [_T("тнг.")] = tng_dot;
	ccys [_T("тенге")] = tenge;
	ccys [_T("казахский тенге")] = kzt;
	ccys [_T("казахские тенге")] = kzt;

	ccys [_T("UAH")] = uah;
	ccys [_T("грн")] = grn;
	ccys [_T("грн.")] = grn_dot;
	ccys [_T("гривна")] = grivna;
	ccys [_T("гривны")] = grivna;

	ccys [_T("BYR")] = byr;
	ccys [_T("бел. руб")] = byr;
	ccys [_T("бел. руб.")] = byr;
	ccys [_T("белорусский рубль")] = byr;
	ccys [_T("белорусские рубли")] = byr;

	return ccys;
}

static const Currencies currencies = BuildCurrencies ();

const TCHAR * CurrencySpellErrorCodeToRussianTextMessage (CurrencySpellErrorCode errorCode)
{
	switch (errorCode)
	{
		// Эти первые два кейса означают ошибку в программе, т.к. и в XLL, и в компоненте 1С 
		// исходным представлением первого аргумента (number) является int или double, 
		// которое преобразуется в строку перед передачей в Spell.
		case CurrencySpellErrorCode_NumberIsEmpty: 
			return _T("Ошибка в Morpher.dll: Строка s пуста.");
		case CurrencySpellErrorCode_NumberIsNotANumber: 
			return _T("Ошибка в Morpher.dll: Строка s не распознана как число.");

		case CurrencySpellErrorCode_NumberIsTooBig: 
			return _T("Слишком большое число. В целой части допустимо не более 30 значащих цифр.");
		case CurrencySpellErrorCode_NumberIsTooSmall: 
			return _T("Слишком много цифр после запятой. В дробной части допустимо не более 30 значащих цифр.");
		case CurrencySpellErrorCode_DeclensionError: 
			return _T("Единица измерения нерусская или не имеет нужной падежной формы.");
		case CurrencySpellErrorCode_RoundingNeeded:
			return _T("Слишком много цифр после запятой. Для большинства валют допустимо не более 2 цифр; для белорусских рублей число должно быть целым.");
		case CurrencySpellErrorCode_Success: 
			break;
		default: break;
	}

	return _T("Ошибка в Morpher.dll: Неучтенный код возврата функции Spell.");
}

static CurrencySpellErrorCode ToCurrencySpellErrorCode(SpellErrorCode errorCode)
{
	switch (errorCode)
	{
		case SpellErrorCode_NumberIsTooLarge:
			return CurrencySpellErrorCode_NumberIsTooBig;
		case SpellErrorCode_DeclensionError: // все встроенные денежные единицы должны склоняться
		case SpellErrorCode_NumberIsEmpty:
		case SpellErrorCode_NumberIsNotANumber:
		case SpellErrorCode_NumberIsTooSmall:
		default:
			throw runtime_error("Ошибка прописи встроенной единицы измерения.");
	}
}

static void MakeFirstUppercase (tstring * s) 
{
	if (s->empty ()) return;

	(*s) [0] = Caps::ToUpper ((*s) [0]);
}

CurrencySpellResult RussianCurrencySpeller::Spell2(const wstring& number, const wstring& unit, int padeg) const
{
	Currencies::const_iterator it = currencies.find(unit);

	bool minus = number[0] == _T('-');

	if (it != currencies.end())
	{
		if (minus)
		{
			CurrencySpellResult r = Spell2(number.substr(1), unit, padeg);

			if (r.errorCode != CurrencySpellErrorCode_Success) return r;

			return CurrencySpellResult(tstring(_T("минус ")) + r.result);
		}

		const Currency& ccy = it->second;

		ParsePositiveNumberResult parseResult = ParsePositiveNumber(number);

		if (parseResult.integer.empty()) return CurrencySpellResult(CurrencySpellErrorCode_NumberIsNotANumber);

		size_t maxFractionDigits = ccy.cents.empty() ? 0 : 2;

		if (parseResult.fraction.size() > maxFractionDigits)
		{
			return CurrencySpellResult(CurrencySpellErrorCode_RoundingNeeded);
		}

		if (parseResult.fraction.empty())
		{
			parseResult.fraction = _T("0");
		}

		tostringstream os;

		bool rub_present = parseResult.integer != _T("0") || parseResult.fraction == _T("0");

		if (rub_present)
		{
			SpellResult spelt = numberSpeller.Spell(parseResult.integer, ccy.name, padeg);

			if (spelt.errorCode != SpellErrorCode_Success) return ToCurrencySpellErrorCode(spelt.errorCode);

			os << spelt.number << _T(" ") << spelt.unit;
		}

		if (! ccy.cents.empty())
		{
			if (rub_present)
			{
				os << _T(" ");
			}

			tstring fraction = parseResult.fraction;

			while (fraction.length() < maxFractionDigits) fraction += _T("0");

			SpellResult spelt = numberSpeller.Spell(fraction, ccy.cents, padeg);

			if (spelt.errorCode != SpellErrorCode_Success) return ToCurrencySpellErrorCode(spelt.errorCode);

			if (!rub_present)
			{
				os << spelt.number;
			}
			else
			{
				os << setfill(_T('0')) << setw(2) << fraction;
			}

			os << _T(" ") << spelt.unit;
		}

		return CurrencySpellResult(os.str());
	}

	SpellResult r = numberSpeller.Spell(minus ? number.substr(1) : number, unit, padeg);

	switch (r.errorCode)
	{
	case SpellErrorCode_Success:
		break;
	case SpellErrorCode_NumberIsTooSmall:
		return CurrencySpellResult(CurrencySpellErrorCode_NumberIsTooSmall);
	case SpellErrorCode_NumberIsTooLarge:
		return CurrencySpellResult(CurrencySpellErrorCode_NumberIsTooBig);
	case SpellErrorCode_DeclensionError:
		return CurrencySpellResult(CurrencySpellErrorCode_DeclensionError);
	case SpellErrorCode_NumberIsNotANumber:
		return CurrencySpellResult(CurrencySpellErrorCode_NumberIsNotANumber);
	case SpellErrorCode_NumberIsEmpty:
		return CurrencySpellResult(CurrencySpellErrorCode_NumberIsEmpty);
	default:
		throw runtime_error ("Неучтенный код возврата SpellErrorCode.");
	}

	tstring spelledNumber = r.number;

	if (minus)
	{
		spelledNumber = _T("минус ") + spelledNumber;
	}

	MakeFirstUppercase(&spelledNumber);

	return CurrencySpellResult(number + _T(" (") + spelledNumber + _T(") ") + r.unit);
}

static wstring FormatDoubleNicely(double d)
{
	std::tostringstream stream;

	typedef numeric_limits<double> dbl;

	stream.precision(dbl::digits10 + 1);
	stream << fixed << d;

	std::tstring s = stream.str();

	size_t iDot = s.find(_T('.'));

	if (iDot != tstring::npos)
	{
		size_t i9 = s.find(_T("9999"), iDot + 1);

		if (i9 != tstring::npos && i9 > iDot + 1)
		{
			s.erase(i9);

			++s[i9 - 1];
		}

		size_t i0 = s.find(_T("0000"), iDot + 1);

		if (i0 != tstring::npos && i0 > iDot + 1)
		{
			s.erase(i0);
		}

		s.erase(s.find_last_not_of(_T("0")) + 1);

		s[iDot] = _T(',');

		if (iDot == s.length() - 1)
		{
			s.erase(iDot);
		}
	}

	return s;
}

class AbbrMorpher : public IMorpher
{
	const IMorpher * morpher;

public:

	AbbrMorpher (const IMorpher * morpher) : morpher (morpher)
	{
	}

	Phrase * Analyse (const tstring & s, Attributes attributes) const override
	{
		Tokenizer t (s, Tokenizer::Russian);

		t.GetNextSeparator ();
		tstring token = t.GetNextToken ();

		typedef map <tstring, RodChislo> Dict;

		Dict d;

		d [_T("руб")] = SingMasc;
		d [_T("коп")] = SingFem;
		d [_T("грн")] = SingFem;
		d [_T("тнг")] = SingMasc;
		d [_T("долл")] = SingMasc;

		Dict::const_iterator i = d.find (token);

		if (i != d.end ())
		{
			IPhrasePart * part = new InvariablePart (s);
			list <Ptr <const IPhrasePart> > parts;
			parts.push_back (part);
			return new Phrase (parts, i->second, false, false, new Phrase (parts, Plural, false, false, nullptr));
		}
		
		return morpher->Analyse (s, attributes);
	}
};

RussianCurrencySpeller::RussianCurrencySpeller(const IMorpher* morpher)
	: abbrMorpher(new AbbrMorpher (morpher))
    , numberSpeller(abbrMorpher)
{
}

RussianCurrencySpeller::~RussianCurrencySpeller()
{
	delete abbrMorpher;
}

CurrencySpellResult RussianCurrencySpeller::Spell(double number, const wstring& unit, int padeg) const
{
	return Spell(FormatDoubleNicely(number), unit, padeg);
}

CurrencySpellResult RussianCurrencySpeller::Spell(const wstring& number, const wstring& unit, int padeg) const
{
	if (number.empty()) return CurrencySpellResult(CurrencySpellErrorCode_NumberIsEmpty);

	CurrencySpellResult r = Spell2(number, unit, padeg);

	MakeFirstUppercase(&r.result);

	return r;
}

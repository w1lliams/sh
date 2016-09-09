#pragma once

#include "../Tokenizer.h"

#include "morpher.h"

#include <memory>
#include <stdexcept>
        
using namespace std;

class ICaseSpeller
{
public:
	virtual ~ICaseSpeller () {}
    virtual void SpellHundreds (int nHundreds) const = 0;
    virtual void SpellTens (int nTens) const = 0;
    virtual void Spell1to19 (int n) const = 0;
};

enum GramCase
{
	Nominative, Genitive, Dative, Accusative, Instrumental, Prepositional, Locative
};

class StringBuilder : public tostringstream
{
	bool spaceRequired;

public:
	StringBuilder () : spaceRequired (false)
	{
	}

	void InsertSpace ()
	{
		if (spaceRequired) *this << ' ';

		spaceRequired = true;
	}
};

class RussianCaseSpeller : public ICaseSpeller
{
    StringBuilder & _sb;
    GramCase _gCase;
    RodChislo _rodChislo;
    bool _isAnimate;
	GramCase _gCaseWithAnimacy;
    bool _isNominativeOrAccusative;

public:
	RussianCaseSpeller (StringBuilder & sb, GramCase gCase, RodChislo rodChislo, bool isAnimate)
		: _sb (sb)
		, _gCase (gCase == Locative ? Prepositional : gCase) // HACK
        , _rodChislo (rodChislo)
        , _isAnimate (isAnimate)
		, _gCaseWithAnimacy ((_gCase == Accusative && _rodChislo != SingNeut && _rodChislo != SingFem) ? _isAnimate ? Genitive : Nominative : _gCase)
		, _isNominativeOrAccusative (_gCase == Nominative || _gCase == Accusative)
    {
    }

    void Append (tstring s) const
    {
        _sb << s;
    }

    void Append (tstring s, size_t start, size_t count) const
    {
        _sb << s.substr (start, count);
    }

    static const TCHAR * zeroToNine [];
    static const TCHAR * TwoThreeFourGenitivePrepositional [];
    static const TCHAR * TwoThreeFourDative [];
    static const TCHAR * TwoThreeFourInstrumental [];
    static const TCHAR * DvoeTroeChetvero [];
    static const TCHAR * DvestiTristaChetyresta [];

    void OneToNine (int n) const
    {
        if (n == 1)
        {
            Append (SpellOne ());
        }
        else TwoToNine (n);
    }

    void TwoToNine (int n) const
    {
        if (_isNominativeOrAccusative)
        {
            Append ((n == 2 && _rodChislo == SingFem && (_gCase == Nominative || !_isAnimate)) 
                        ? _T("две")
                        : n < 5  
                                ? _rodChislo == Plural && !(_isAnimate && _gCase == Accusative)
                                    ? DvoeTroeChetvero [n-2] 
                                    : _isAnimate && _gCase == Accusative
                                            ? TwoThreeFourGenitivePrepositional [n-2]
                                            : zeroToNine [n]
                                : zeroToNine [n]);
        }
        else if (n < 5) // 2,3,4
        {
            const TCHAR ** forms = 0;

            switch (_gCaseWithAnimacy)
            {
                case Genitive: 
                case Prepositional:
                    forms = TwoThreeFourGenitivePrepositional;
                    break;
                case Dative: 
                    forms = TwoThreeFourDative;
                    break;
                case Instrumental: 
                    forms = TwoThreeFourInstrumental;
                    break;
                case Nominative:
                case Accusative:
                case Locative:
                    break;
            }

            Append (forms [n - 2]);
        }
        else // 5,6,7,8,9 в косвенных кроме винительного
        {
            Spell56789 (n);
        }
    }

    void Spell56789 (int n) const
    {
        tstring form = zeroToNine [n];

        if (n == 8 && !(_gCase == Nominative || _gCase == Accusative))
        {
            form = _T("восьм"); // учет беглой гласной в слове восемь
        }
        else
        {
            form = form.substr (0, form.length () - 1); // не добавлять Ь
        }

        Append (form);

        WriteThridDeclensionEnding ();
    }

    static const TCHAR * OdinEndings [];
    static const TCHAR * OdnaEndings [];
    static const TCHAR * OdniEndings [];

    tstring SpellOne () const
    {
        if (_rodChislo == SingNeut && _isNominativeOrAccusative)
        {
            return _T("одно");
        }

        const TCHAR ** forms = 0;

        switch (_rodChislo)
        {
            case SingMasc:
            case SingNeut:      
                forms = OdinEndings; break;
            case SingFem:       
                forms = OdnaEndings; break;
            case Plural: 
                forms = OdniEndings; break;
            case RodChislo_Count:
                break;
        }

        return tstring (_T("од")) + forms [(int) _gCaseWithAnimacy];
    }

    void AppendHundreds (int nHundreds) const
    {
        if (nHundreds == 1)
        {
            Append (_T("ст"));
            SpellEndingOorA ();
        }
        else if (_isNominativeOrAccusative && nHundreds <= 4)
        {
            Append (DvestiTristaChetyresta [nHundreds-2]);
        }
        else
        {
            OneToNine (nHundreds);
            Append (_T("с"));
            Append (StoIndirectEndings [(int) _gCase]);
        }
    }

    static const TCHAR * StoIndirectEndings [];

    void SpellEndingOorA () const
    {
        Append (_isNominativeOrAccusative ? _T("о") : _T("а"));
    }

    /// <param name="единиц">0-9</param>
    void Spell10to19 (int lastDigit) const
    {
        if (lastDigit == 0)
        {
            Append (_T("десят"));
        }
        else
        {
            const TCHAR * stem = 0;

            switch (lastDigit)
            {
                case 1:
                    stem = _T("один");
                    break;
                case 2:
                    stem = _T("две");
                    break;
                case 3:
                    stem = _T("три");
                    break;
            }

            if (stem)
            {
                Append (stem);
            }
            else
            {
                tstring s = zeroToNine [lastDigit];

                Append (s, 0, s.length () - 1); // не добавлять Ь или Е в слове ЧЕТЫРЕ
            }

            Append (_T("надцат"));
        }

        WriteThridDeclensionEnding ();
    }

    void WriteThridDeclensionEnding () const
    {
        tstring ending;

        switch (_gCase)
        {
            case Nominative: 
            case Accusative: 
                ending = _T("ь"); break; // тринадцать
            case Instrumental: 
                ending = _T("ью"); break; // тринадцатью
            default: ending = _T("и"); break; // тринадцати
        }

        Append (ending);
    }

    /// <param name="n">2-9</param>
    void AppendTens (int n) const
    {
        switch (n)
        {
            case 2:
            case 3:
                Append (zeroToNine [n]);
                Append (_T("дцат"));
                WriteThridDeclensionEnding ();
                break;
            case 4:
                Append (_isNominativeOrAccusative ? _T("сорок") : _T("сорока"));
                break;
            case 9:
                Append (_T("девяност"));
                SpellEndingOorA ();
                break;
            default:
                Spell56789 (n);
                Append (_T("десят"));
                if (! _isNominativeOrAccusative) WriteThridDeclensionEnding ();
                break;
        }
    }

    virtual void SpellHundreds (int nHundreds) const
    {
        AppendHundreds (nHundreds);
    }

    virtual void SpellTens (int nTens) const
    {
        AppendTens (nTens);
    }

    virtual void Spell1to19 (int n) const
    {
        if (n < 10) OneToNine (n); else Spell10to19 (n - 10);
    }
};

enum SpellErrorCode
{
	SpellErrorCode_Success,
	SpellErrorCode_NumberIsEmpty,
	SpellErrorCode_NumberIsNotANumber,
	SpellErrorCode_NumberIsTooSmall,
	SpellErrorCode_NumberIsTooLarge,
	SpellErrorCode_DeclensionError
};

struct SpellResult
{
	SpellResult (SpellErrorCode errorCode) : errorCode (errorCode), number (_T("")), unit (_T("")) {}
	SpellResult (tstring number, tstring unit) : errorCode (SpellErrorCode_Success), number (number), unit (unit) {}

	SpellErrorCode errorCode;
	tstring number;
	tstring unit;
};

struct ParsePositiveNumberResult
{
	tstring integer, fraction;
};

// returns:
// index of decimal point or
// -1 if s is not recognized as a non-negative number.
static int FindDecimalPoint (const std::tstring & s)
{
	size_t i = s.find_first_not_of(_T("0123456789"));

	if (i == std::tstring::npos) return (int) s.length ();

	if (s [i] != _T ('.') && s [i] != _T (',')) return -1;

	size_t j = s.find_first_not_of(_T("0123456789"), i+1);

	return (j == std::tstring::npos) ? (int) i : -1;
}

static ParsePositiveNumberResult ParsePositiveNumber (const tstring & number)
{
	int i = FindDecimalPoint (number);

	ParsePositiveNumberResult r;

	if (i < 0) return r;

	tstring whole = number.substr (0, i);
	if (whole.empty ()) whole = _T("0");

	tstring fraction = (unsigned) i == number.length () ? _T("") : number.substr (i+1);

	fraction.erase (fraction.find_last_not_of (_T("0")) + 1);

	r.integer = whole;
	r.fraction = fraction;

	return r;
}

class NumberSpeller
{
public:
	virtual ~NumberSpeller () {}

	virtual const TCHAR * Minus () const = 0;

	SpellResult Spell (const tstring & number, tstring unit, int padeg) const
	{
		if (number.empty ()) return SpellResult (SpellErrorCode_NumberIsEmpty);

		if (number [0] == '-') 
		{
			SpellResult r = Spell (number.substr (1), unit, padeg);

			if (r.errorCode == 0)
			{
				tstring num = Minus ();

				num.append (_T(" "));

				num.append (r.number);

				r.number = num;
			}

			return r;
		}

		ParsePositiveNumberResult parseResult = ParsePositiveNumber (number);

		tstring whole = parseResult.integer;
		tstring fraction = parseResult.fraction;

		if (whole.empty ()) return SpellResult (SpellErrorCode_NumberIsNotANumber);

		if (! fraction.empty ())
		{
            tstring unit_rod = GenitiveSingular (unit);

            if (unit_rod.empty ()) return SpellResult (SpellErrorCode_DeclensionError);

            tstring wholeUnit = _T("целая"); // TODO: для украинского

			SpellResult wholeResult = Spell (whole, wholeUnit, padeg);

			if (wholeResult.errorCode != 0) return wholeResult;

            tstring fractionalUnit = Ordinal (fraction.length ()); // TODO: для украинского

			if (fractionalUnit.empty ()) return SpellResult (SpellErrorCode_NumberIsTooSmall);

			SpellResult fractionResult = Spell (fraction, fractionalUnit, padeg);

			if (fractionResult.errorCode != 0) return fractionResult;

            return SpellResult (wholeResult.number + _T(" ") + wholeResult.unit + _T(" ")
                + fractionResult.number + _T(" ") + fractionResult.unit, unit_rod);
		}

		size_t rank = (whole.length () + 2) / 3;
		
        if (whole == _T("0"))
        {
            tstring unit_rod_mnozh = GenitivePlural (unit);

            return unit.empty () ? SpellResult (SpellErrorCode_DeclensionError) : SpellResult (SpellZero (padeg), unit_rod_mnozh);
        }

		int triple;
			
		StringBuilder sb;

		for (;;)
		{
			--rank;

			int i = (int) (whole.length () - rank * 3);

			triple = 0;

			if (i > 0) triple += (whole [--i] - _T('0')) * 1;
			if (i > 0) triple += (whole [--i] - _T('0')) * 10;
			if (i > 0) triple += (whole [--i] - _T('0')) * 100;

			if (rank == 0) break;

			if (triple > 0)
			{
                tstring rankUnit = GetUnit (rank - 1);

				if (rankUnit.empty ()) return SpellResult (SpellErrorCode_NumberIsTooLarge);

				SpellTriple (sb, triple, &rankUnit, padeg);

				if (rankUnit.empty ()) return SpellResult (SpellErrorCode_DeclensionError);

				sb.InsertSpace ();
				sb << rankUnit;
			}
		}
		
		SpellTriple (sb, triple, &unit, padeg);

		return unit.empty () ? SpellResult (SpellErrorCode_DeclensionError) : SpellResult (sb.str (), unit);
	}

	void SpellTriple (StringBuilder & sb, int n, tstring * unit, int padeg) const
	{
        auto_ptr <ICaseSpeller> lang = auto_ptr <ICaseSpeller> (CreateSpeller (sb, n, padeg, unit));

        if (!lang.get ())
        {
            *unit = _T("");
            return;
        }

        if (n > 0)
        {
            SpellTriple (lang.get (), sb, n);
        }
	}

    static void SpellTriple (ICaseSpeller * lang, StringBuilder & sb, int n)
    {
        if (n / 100 > 0)
        {
            sb.InsertSpace ();
            lang->SpellHundreds (n / 100);
        }

        n %= 100; // от сотен избавились

        if (n > 0 && n < 20)
        {
            sb.InsertSpace ();
            lang->Spell1to19 (n);
        }
        else // десятки и единицы раздельно
        {
            if (n / 10 > 0) 
            {
                sb.InsertSpace ();
                lang->SpellTens (n / 10);
            }
                
            n %= 10;

            if (n > 0) 
            {
                sb.InsertSpace ();
                lang->Spell1to19 (n);
            }
        }
    }

    virtual ICaseSpeller * CreateSpeller (StringBuilder & sb, int n, int padeg, tstring * unit) const = 0;

    /// <summary> Получить название класса с заданным рангом. </summary>
    /// <param name="rank">0 = тысяча, 1 = миллион, 2 = миллиард и т.д. </param>
    virtual const tstring GetUnit (size_t rank) const = 0;
	virtual const TCHAR * SpellZero (int padeg) const = 0;
	virtual const tstring GenitivePlural (const tstring & unit) const = 0;

	const tstring Ordinal (size_t exponent) const
	{
		tstring stem = OrdinalStem (exponent);

		if (stem.empty ()) return stem;

        return stem + _T("ая");
    }

    const tstring OrdinalStem (size_t exponent) const
    {
        switch (exponent)
        {
            case 1: return _T("десят");
            case 2: return _T("сот");
        }

        size_t rank = exponent / 3 - 1;

        tstring unit = rank == 0 ? _T("тысяч") : GetUnit (rank);

		if (unit.empty ()) return unit;

        unit += _T("н"); // тысячНая, миллионНая...

        switch (exponent % 3)
        {
            case 1: unit = _T("десяти") + unit; break;
            case 2: unit = _T("сто") + unit; break;
        }

        return unit;
    }

	virtual const tstring GenitiveSingular (const tstring & unit) const = 0;
};

class PhraseWrapper 
{
public:
	const Phrase * phrase;

	PhraseWrapper (Phrase * phrase) : phrase (phrase) {}

	~PhraseWrapper ()
	{
		if (phrase)
		{
			if (phrase->Plural ()) delete phrase->Plural ();

			delete phrase;
		}
	}
};

class RussianNumberSpeller : public NumberSpeller
{
	const IMorpher * morpher;

public:

	RussianNumberSpeller (const IMorpher * morpher)
		: morpher (morpher)
	{
		if (!morpher) throw runtime_error ("morpher is nullptr.");
	}

	virtual const TCHAR * Minus () const { return _T("минус"); }

	virtual const tstring GenitiveSingular (const tstring & unit) const 
	{
		PhraseWrapper phraseWrapper = morpher->Analyse (unit, (Attributes) 0);

		return phraseWrapper.phrase ? phraseWrapper.phrase->getRod () : _T("");
	}

	virtual const tstring GenitivePlural (const tstring & unit) const 
	{
		PhraseWrapper phraseWrapper = morpher->Analyse (unit, (Attributes) 0);

		return phraseWrapper.phrase && phraseWrapper.phrase->Plural () ? phraseWrapper.phrase->Plural ()->getRod () : _T("");
	}

	virtual const TCHAR * SpellZero (int padeg) const 
	{
        static const TCHAR * zero [] = {_T("ноль"), _T("ноля"), _T("нолю"), _T("ноль"), _T("нолём"), _T("ноле"), _T("ноле")};

		return zero [padeg];
	}

    virtual const tstring GetUnit (size_t rank) const
	{
        static const TCHAR * ranks [] = {_T("тысяча"), _T("миллион"), _T("миллиард"), _T("триллион"),
			_T("квадриллион"), _T("квинтиллион"), _T("секстиллион"), _T("септиллион"), _T("октиллион")};

		return rank < sizeof (ranks) / sizeof (*ranks) ? ranks [rank] : _T("");
	}

    static bool IsAniateAndAnimacyMattersFor (const Phrase * parse, int n)
    {
        return parse->IsAnimate () && AnimacyMattersFor (n);
    }

    static bool AnimacyMattersFor (int n)
    {
        return n < 5 || (n % 10 == 1 && n != 11);
    }

    static tstring GetUnit (GramCase gCase, int n, const Phrase * parse)
    {
        bool plural;

        if (gCase == Nominative || (gCase == Accusative && !IsAniateAndAnimacyMattersFor (parse, n)))
        {
            int nTens = (n % 100)/10;
            if (nTens == 1)
            {
                gCase = Genitive;
                plural = true;
            }
            else
            {
                switch (n%10)
                {
                    case 1:
                        plural = false;
                        break;
                    case 2:
                    case 3:
                    case 4:
                        return parse->PaucalForm ();
                    default: // 5,6,7,8,9,0
                        gCase = Genitive;
                        plural = true;
                        break;
                }
            }
        }
        else
        {
            plural = (n % 10) != 1 || (n % 100 == 11);

            if (n == 0) gCase = Genitive;
        }

		if (plural && parse->getRodChislo () != Plural)
		{
			parse = parse->Plural ();

			if (parse == 0) 
			{
				return _T("");
			}
		}

        return parse->GetForm ((Padeg) gCase);
    }

	class SpecialUnit : public IPhrasePart
	{
		const TCHAR * * cases;

	public:
		SpecialUnit (const TCHAR * * cases)
			: cases (cases)
		{}

		tstring GetFormByCase(Padeg padeg, bool /*animate*/) const
		{
			return cases [padeg];
		}

		bool PaulcalIsSingular() const
		{
			return true;
		}
	};

	static const list <Ptr <const IPhrasePart> > MakeList (const Ptr <const IPhrasePart> & elem)
	{
		list <Ptr <const IPhrasePart> > list;

		list.push_back (elem);

		return list;
	}

	Phrase* ParseUnit(const tstring & unit) const
	{
		static const TCHAR * manSing [] = {_T("человек"), _T("человека"), _T("человеку"), _T("человека"), _T("человеком"), _T("человеке"), _T("человеке")};
		static const TCHAR * manPlur [] = {_T("люди"), _T("человек"), _T("человекам"), _T("человек"), _T("человеками"), _T("человеках"), _T("человеках")};

		if (unit == _T("человек")) return new Phrase (MakeList (new SpecialUnit (manSing)), SingMasc, true, false, 
			new Phrase (MakeList (new SpecialUnit (manPlur)), Plural, true, false, 0));

		static const TCHAR * yearSing [] = {_T("год"), _T("года"), _T("году"), _T("год"), _T("годом"), _T("годе"), _T("году")};
		static const TCHAR * yearPlur [] = {_T("года"), _T("лет"), _T("годам"), _T("лет"), _T("годами"), _T("годах"), _T("годах")};

		if (unit == _T("год")) return new Phrase (MakeList (new SpecialUnit (yearSing)), SingMasc, false, false, 
			new Phrase (MakeList (new SpecialUnit (yearPlur)), Plural, false, false, 0));

		return morpher->Analyse (unit, (Attributes) 0);
	}

	virtual ICaseSpeller * CreateSpeller (StringBuilder & sb, int n, int padeg, tstring * unit) const 
	{
		PhraseWrapper phraseWrapper = ParseUnit(*unit);

		const Phrase * phrase = phraseWrapper.phrase;

        if (phrase == 0) return 0;

        *unit = GetUnit ((GramCase) padeg, n, phrase);

		if (unit->empty ()) 
			return 0;

		return new RussianCaseSpeller (sb, (GramCase) padeg, phrase->getRodChislo (), IsAniateAndAnimacyMattersFor (phrase, n));
	}
};


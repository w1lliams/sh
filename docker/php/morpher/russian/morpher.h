#pragma once

#include <set>
#include <map>
#include <list>
#include <string>
#include <vector>
#include <algorithm>
#include <sstream>
#include <stdexcept>
#include <time.h>

#include "../gc.h"
#include "../Tokenizer.h"
#include "exceptionDict.h"

using namespace std;

using namespace GC;

// defined in dict.cpp
extern char dict [];
extern int dict_size;

// defined in fioDict.cpp
extern char fioDict [];
extern int fioDict_size;

// defined in specialDict.cpp
extern char specialDict [];
extern int specialDict_size;

// defined in specialAdjDict.cpp
extern char specialAdjDict [];
extern int specialAdjDict_size;


enum RodChislo // C#: РодЧисло
{
	SingMasc,
	SingFem,
	SingNeut,
	Plural,
	RodChislo_Count
};

enum Padeg // C#: Падеж
{
	Padeg_I,
	Padeg_R,
	Padeg_D,
	Padeg_V,
	Padeg_T,
	Padeg_P,
	Padeg_M
};

enum Attributes // C#: Признак
{
	Attribute_Masc = 1,
	Attribute_Fem = 2,
	Attribute_Fio = 4,
	Attribute_Naric = 8,
	Attribute_Toponym = 16
};

enum EndingsType // C#: ТипСклонения
{
	EndingsType_NounLike,    // Субстантивный
	EndingsType_Adjectival,  // Адъективный
	EndingsType_Uninflected, // Uninflected
	EndingsType_Pronomial    // Местоимённый
};

enum FioPart
{
	FioPart_FamilyName, // Фамилия
	FioPart_GivenName,  // Имя
	FioPart_Patronymic  // Отчество
};

enum Gender
{
	Gender_Masculine,
	Gender_Feminine,
	Gender_Unisex
};

struct Suffix
{
    tstring Morphs;
    FioPart fioPart;
    Gender gender;
};

static Suffix suffixes [] = 
{
    // Порядок перечисления важен.
    {_T("ович"), FioPart_Patronymic, Gender_Masculine},
    {_T("овна"), FioPart_Patronymic, Gender_Feminine},
    {_T("ов,цын,ин"), FioPart_FamilyName, Gender_Masculine},
    {_T("ова,цына,ина"), FioPart_FamilyName, Gender_Feminine},
    {_T("ой,ый"), FioPart_FamilyName, Gender_Masculine},
    {_T("ая"), FioPart_FamilyName, Gender_Feminine},
    {_T("ых"), FioPart_FamilyName, Gender_Unisex}
};

static bool EndsWith (
	tstring::const_reverse_iterator si, 
	tstring::const_reverse_iterator s_rend, 
	tstring::const_reverse_iterator ei, 
	tstring::const_reverse_iterator e_rend)
{
	if (e_rend - ei > s_rend - si) return false; // длина endings больше длины слова

	while (ei != e_rend)
	{
		if (*si++ != *ei++) return false;
	}

	return true;
}

static bool EndsWith (const tstring & s, const tstring & ending)
{
	return EndsWith (s.rbegin (), s.rend (), ending.rbegin (), ending.rend ());
}


static bool EndsWith (const tstring & lemma, TCHAR c)
{
	return !lemma.empty () && *lemma.rbegin () == c;
}

static bool EndsWithOneOf (
	tstring::const_reverse_iterator s,
	tstring::const_reverse_iterator rend,
	const tstring & endings)
{
	tstring::const_reverse_iterator eb = endings.rbegin ();
	tstring::const_reverse_iterator ee = eb;

	for (; eb != endings.rend (); eb = ee)
	{
		while (ee != endings.rend () && *ee != _T(',')) ++ee;

		if (EndsWith (s, rend, eb, ee)) return true;

		if (ee != endings.rend ()) ++ee; // skip the comma
	}

	return false;
}

static bool EndsWithOneOf (
	const tstring & s,
	const tstring & endings)
{
	return EndsWithOneOf (s.rbegin (), s.rend (), endings);
}

// Определение того, что лемма имеет заданное окончание
// с учётом особенностей орфографии.  Так, слово "баня"
// признаётся имеющим окончание "а".
// Также проверяет, что в основе достаточно букв.
// C#: Common.HasEndings.
static bool HasEnding (const tstring & lemma, tstring::const_reverse_iterator eb, tstring::const_reverse_iterator ee) 
{
    if (ee - eb == 0) return true;
	if (ee - eb >= (int) lemma.length ()) return false;

    // сравнение всех, кроме первой, букв окончания 
    // с соответствующими буквами леммы
	tstring::const_reverse_iterator si = lemma.rbegin ();
	tstring::const_reverse_iterator ei = eb;
	for (int i=0; i+1 < ee - eb; ++i) {
		if (*ei++ != *si++) return false;
	}

    // сравнение первой буквы окончания
    TCHAR c = *si;
    if (*ei == c) return true; // полное совпадение
    switch (*ei) {
        case _T('а'): return (c==_T('я'));
		case _T('о'): return (c==_T('е') || c==_T('ё'));
        case _T('у'): return (c==_T('ю'));
        case _T('ы'): return (c==_T('и'));
    }
	return false;
}

static bool HasEnding (const tstring & lemma, const tstring & ending) 
{
	return HasEnding (lemma, ending.rbegin (), ending.rend ());
}

class Common
{
public:
    static const tstring vowels;

    static bool IsVowel (TCHAR c) {
        return vowels.find (c) != tstring::npos;
    }

    static bool ContainsVowel (const tstring & s) {
        return s.find_first_of (vowels) != tstring::npos;
    }

    /// <summary>
    /// Включая Й, исключая Ъ и Ь.
    /// </summary>
    static bool IsConsonant (TCHAR c)
    {
        return tstring (_T("бвгджзйклмнпрстфхцчшщ")).find (c) != tstring::npos;
    }

    static bool HasFamilyNameSuffix (const tstring & lowerCaseWord)
    {
        return HasPronomialFamilyNameSuffix (lowerCaseWord) 
            || EndsWithOneOf (lowerCaseWord, _T("ко,их,ых,ян"));
    }

    static bool HasPronomialFamilyNameSuffix (const tstring & lowerCaseWord)
    {
        return EndsWithOneOf (lowerCaseWord, _T("ов,ев,ёв,ин,цын,ова,ева,ёва,ина,цына"));
    }
};

static bool HasEnding2 (const tstring & word, tstring::const_reverse_iterator eb, tstring::const_reverse_iterator ee)
{
    if (! HasEnding (word, eb, ee)) return false;

    TCHAR lastLetterOfStem = word [word.length () - (ee - eb) - 1];

    if (word.length () == (size_t) (ee - eb) + 1)
    {
        // Всякие Ма, па не склоняются.
        // Чих, Лев, Рой не имеют окончаний -их, -ев, -ой.
        // Щи склоняются.
        if (Common::IsVowel (*(ee-1)) && !Common::IsVowel (lastLetterOfStem) && lastLetterOfStem != _T('щ')) return false;
    }

    // Боа, Иов не имеют окончания -а и -ов.
    TCHAR firstLetterOfEnding= word [word.length () - (ee - eb)];
    if (Common::IsVowel (lastLetterOfStem) && (firstLetterOfEnding== _T('а') || firstLetterOfEnding== _T('о'))) return false;

    return true;
}

static bool HasEndings (const tstring & lemma, const tstring & endings) 
{
	tstring::const_reverse_iterator eb = endings.rbegin ();
	tstring::const_reverse_iterator ee = eb;

	for (; eb != endings.rend (); eb = ee)
	{
		while (ee != endings.rend () && *ee != _T(',')) ++ee;

		if (HasEnding2 (lemma, eb, ee)) return true;

		if (ee != endings.rend ()) ++ee; // skip the comma
	}

	return false;
}

static bool HasAdjectivalEnding (
	tstring::const_reverse_iterator s,
	tstring::const_reverse_iterator send) 
{
	return EndsWithOneOf (s, send, _T("ый,ий,ой,ая,яя,ое,ее,ые,ие"));
}

static bool HasParticipalSuffix (
	tstring::const_reverse_iterator s,
	tstring::const_reverse_iterator send) 
{
	return EndsWithOneOf (s, send, _T("ущ,ющ,ащ,ящ,ш"));
}

static bool Participle (tstring::const_reverse_iterator rbegin, tstring::const_reverse_iterator rend) 
{
	if (! HasAdjectivalEnding (rbegin, rend)) return false;

	return HasParticipalSuffix (rbegin + 2, rend);
}

static bool Participle (const tstring & s)
{
	return Participle (s.rbegin (), s.rend ());
}

/// <summary> Проверяет, является ли данное слово возвратным причастием. </summary>
/// <param name="слово"> Слово в именительном падеже маленькими буквами. </param>
static bool ReflexiveParticiple (const tstring & lowerCaseWord) 
{
	if (lowerCaseWord.length () < 5) return false;

    if (! EndsWith (lowerCaseWord, _T("ся"))) return false;

	return Participle (lowerCaseWord.rbegin () + 2, lowerCaseWord.rend ());
}

class DecoratedWord 
{
	tstring lemma;

	Caps caps;

	bool yer;
	
	bool sya;

	DecoratedWord (const tstring & lemma, Caps caps, bool yer, bool sya) :
		lemma (lemma), caps (caps), yer (yer), sya (sya)
	{
	}

public:

	static DecoratedWord Get (tstring word)
	{
        Caps caps = Caps::GetCaps (word);

		Caps::ToLower (word);

        bool yer = word.length () > 1 && EndsWith (word, _T("ъ"));

        if (yer) word = word.substr (0, word.length () - 1);

		// отщепление аффикса -СЯ у возвратных причастий
        bool sya = ReflexiveParticiple (word);
        if (sya) word = word.substr (0, word.length () - 2);

        return DecoratedWord (word, caps, yer, sya);
	}

    static bool YerAllowed (const tstring & form)
    {
        return tstring (_T("бвгдзклмнпрстфхжшчц")).find (*form.rbegin ()) != tstring::npos;
    }

	tstring ApplyTo (const tstring & word) const
    {
		tstring s = word;

        if (yer && YerAllowed (s)) s += _T('ъ');

        if (sya) s += _T("ся");

        caps.Apply (s);

		return s;
    }

	const tstring & Lemma () const
	{
		return lemma;
	}

	const Caps & GetCaps () const
	{
		return caps;
	}

	const tstring DecoratedLemma () const
	{
		return ApplyTo (lemma);
	}

    const DecoratedWord Clone (const tstring & newLemma) const
    {
        return DecoratedWord (newLemma, caps, sya, yer);
    }
};

static const tstring Join (const vector <DecoratedWord> & v, const tstring & separator)
{
	if (v.empty ()) return _T("");

	if (v.size () == 1) return (*v.begin ()).Lemma ();

	size_t total = 0;

	for (vector <DecoratedWord>::const_iterator s = v.begin (); s != v.end (); ++s)
	{
		total += s->Lemma ().length () + separator.length ();
	}

	if (total == 0) return _T("");

	tstring js;

	js.reserve (total - separator.length ());

	for (vector <DecoratedWord>::const_iterator s = v.begin (); s != v.end (); ++s)
	{
		if (s != v.begin ()) js += separator;

		js += s->Lemma ();
	}

	return js;
}

class MultiPartDecoratedWord
{
	vector <DecoratedWord> decoratedWords;
	tstring lemma;

public:
	
	MultiPartDecoratedWord (const vector <DecoratedWord> & decoratedWords)
		: decoratedWords (decoratedWords), lemma (Join (decoratedWords, _T("-")))
    {
    }

    const tstring & LastPart () const
    {
        return decoratedWords [decoratedWords.size () - 1].Lemma ();
    }

    const tstring & FirstPart () const
    {
        return decoratedWords [0].Lemma ();
    }

    const vector <DecoratedWord> & Parts () const
    {
        return decoratedWords;
    }

    size_t PartCount () const
    {
        return decoratedWords.size ();
    }

	const tstring Lemma () const
	{
		return lemma;
	}

    bool IsSameCaps (const Caps & caps) const
    {
		for (vector <DecoratedWord>::const_iterator part = Parts ().begin (); part != Parts ().end (); ++part)
		{
            if (PartCount () > 1 && part->Lemma ().length () < 3) continue; // Ортега-и-Гассет

			if (part->Lemma ().compare (_T("оглы")) == 0) continue;
			if (part->Lemma ().compare (_T("кызы")) == 0) continue;

            if (! part->GetCaps ().IsSameType (caps)) return false;
        }

        return true;
    }
};

class TokenizedString
{
    list <MultiPartDecoratedWord> words;
    list <tstring> delims;

public:
	TokenizedString (const list <MultiPartDecoratedWord> & words, const list <tstring> & delims)
		: words (words), delims (delims)
    {
    }

    list <MultiPartDecoratedWord> & Words () 
    {
        return words;
    }

    list <tstring> & Delims () 
    {
        return delims; 
    }
};

class IPhrasePart : public virtual Object
{
public:

    virtual tstring GetFormByCase (Padeg padeg, bool animate) const = 0;

    virtual bool PaulcalIsSingular () const = 0;
};
    
class InvariablePart : public IPhrasePart
{
	tstring s;

public:

	InvariablePart (const tstring & s) : s (s)
	{
	}

    virtual tstring GetFormByCase (Padeg padeg, bool animate) const
	{
		return s;
	}

    virtual bool PaulcalIsSingular () const
	{
		return false;
	}
};

class PhraseBuilder // C#: PhraseBuilder
{
    list <Ptr <const IPhrasePart> > parts;

public:

    const list <Ptr <const IPhrasePart> > & Parts ()
    {
        return parts; 
    }

    void Add (const tstring & s)
    {
        if (s.empty ()) return;

        Add (new InvariablePart (s));
    }

    void Add (Ptr <const IPhrasePart> part)
    {
		assert (part);

        parts.push_back (part);
    }
};

class IParsedPart : public virtual Object
{
public:
    virtual bool AppendTo (PhraseBuilder & phraseBuilder, list <tstring>::const_iterator & , bool plural) const = 0;
};

class Phrase // C#: Склоняемое
{
	const list <Ptr <const IPhrasePart> > parts;
    RodChislo rodChislo;
    bool isAnimate;
    bool prepositionNA;
	const Phrase * pluralPhrase;

public:

	Phrase (const list <Ptr <const IPhrasePart> > & parts, 
            RodChislo rodChislo,
            bool isAnimate,
            bool prepositionNA,
			const Phrase * pluralPhrase) 

		: parts (parts),
		rodChislo (rodChislo),
		isAnimate (isAnimate),
		prepositionNA (prepositionNA),
		pluralPhrase (pluralPhrase)
	{
	}

	Phrase (const Phrase * phrase1, const Phrase * phrase2)

		: parts (Concat (phrase1->parts, phrase2->parts)),
		rodChislo (phrase1->rodChislo),
		isAnimate (phrase1->isAnimate),
		prepositionNA (phrase1->prepositionNA),
		pluralPhrase (phrase1->pluralPhrase && phrase2->pluralPhrase ? new Phrase (phrase1->pluralPhrase, phrase2->pluralPhrase) : 0)
	{
	}

	static const list <Ptr <const IPhrasePart> > Concat (
		const list <Ptr <const IPhrasePart> > list1, 
		const list <Ptr <const IPhrasePart> > list2)
	{
		list <Ptr <const IPhrasePart> > result;
		result.insert (result.end (), list1.begin (), list1.end ());
		result.insert (result.end (), list2.begin (), list2.end ());
		return result;
	}

	const tstring getImen  () const {return GetForm (Padeg_I);}
	const tstring getRod   () const {return GetForm (Padeg_R);}
	const tstring getDat   () const {return GetForm (Padeg_D);}
	const tstring getVin   () const {return GetForm (Padeg_V);}
	const tstring getTvor  () const {return GetForm (Padeg_T);}
	const tstring getPred  () const {return GetForm (Padeg_P);}
	const tstring getPredO () const {return GetPrepositional (&Phrase::ChoosePrepositionO, Padeg_P);}
	const tstring getMest  () const {return GetPrepositional (&Phrase::ChoosePrepositionVorNA, Padeg_M);}

	RodChislo getRodChislo () const {return rodChislo;}

	bool IsAnimate () const {return isAnimate;}

	const Phrase * Plural () const {return pluralPhrase;}

private:

    /// <param name="word">слово перед предлогом</param>
    typedef const TCHAR * (Phrase::*ChoosePrepositionDelegate) (const tstring & word) const;

    tstring GetPrepositional (ChoosePrepositionDelegate choosePreposition, Padeg padeg) const
    {
        return AddPreposition (GetForm (padeg), choosePreposition);
    }

    tstring AddPreposition (const tstring & form, ChoosePrepositionDelegate choosePreposition) const
    {
        if (form.empty ()) return form;

        Tokenizer t (form, Tokenizer::Russian);

        // Взять 2 первых слова.
        tstring firstSeparator = t.GetNextSeparator ();
        tstring w0 = t.GetNextToken ();
        t.SkipNextSeparator ();
        tstring w1 = t.GetNextToken ();
		Caps::ToLower (w0);
		Caps::ToLower (w1);

        // "ни о ком, ни о чём, кое о ком, кое о чём"
        if ((w0 == _T("ни") || w0 == _T("кое")) && w1 == _T("о")) return form;

        // добавить предлог о/об/обо перед первым словом
        size_t i = firstSeparator.length ();

        // Предлог должен стоять перед кавычкой: о "Варяге", но (о Варяге).
        if (EndsWithOneOf (firstSeparator, _T("\",',«,“,”"))) --i;

        tstring preposition = (this->*choosePreposition) (w0);

        // Если вся фраза большими буквами, то и предлог сделать тоже.
        if (Caps::IsUpper (form.begin (), form.end ())) Caps::ToUpper (preposition);

        return form.substr (0,i) + preposition + _T(' ') + form.substr (i);
    }

    const TCHAR * ChoosePrepositionO (const tstring & firstWord) const
	{
		static const TCHAR * exceptions [] = 
		{
            _T("мне"),
            _T("всём"),
            _T("всем") // "всём" без точек над Ё
		};

		for (size_t i=0; i < sizeof (exceptions) / sizeof (*exceptions); ++i)
		{
			if (firstWord == exceptions [i]) return _T("обо");
		}

#ifdef _UNICODE
        tstring vowels = _T("аоуэиыaeiouαεοω");
#else
        tstring vowels = _T("аоуэиыaeio");
#endif

        // буквы, названия которых начинаются с гласной, e.g. "эм", "икс"
        tstring consonants = _T("лмнсфfhlmnrsx");

        bool containsVowel = firstWord.find_first_of (vowels + _T("яёею")) != tstring::npos;

        return (containsVowel ? vowels : consonants).find_first_of (firstWord [0]) == tstring::npos ? _T("о") : _T("об");
	}

    const TCHAR * ChoosePrepositionVorNA (const tstring & firstWord) const
	{
        return prepositionNA ? _T("на") : ChoosePrepositionVorVO (firstWord);
	}

    const TCHAR * ChoosePrepositionVorVO (const tstring & firstWord) const
	{
		static const TCHAR * exceptions [] = 
		{
            _T("дворе"), 
            _T("дворах"), // но: в дверях
            _T("рту"),
            _T("ртах"),
            _T("сне"),
            _T("снах"),
            _T("ржи"), // но: в ржавчине
            _T("ржах"),
            _T("мне"), // но: в мнении, в мнимой величине
		};

		for (size_t i=0; i < sizeof (exceptions) / sizeof (*exceptions); ++i)
		{
			if (firstWord == exceptions [i]) return _T("во");
		}

        if (firstWord.length () > 3 && (firstWord [0] == _T('в') || firstWord [0] == _T('ф')) && !Common::IsVowel (firstWord [1]))
        {
            // во времени, во власти, во Владимире, во Франции, во Флоренции
            return _T("во");
        }

        // во Львове, во Льгове

		// TODO
        //return (StartsWithOneOf (firstWord, "ль,мц")) ? "во" : "в";

		return _T("в");
	}

public:
	const tstring GetForm (Padeg padeg) const
	{
        tstring s = _T("");

		for (list <Ptr <const IPhrasePart> >::const_iterator part = parts.begin (); part != parts.end (); ++part)
        {
            // Вижу животное, дитятко, привидение (сред.род) - склоняются как неодушевлённые.
            // Вижу животных, дитяток, привидений (мн.ч) - как одушевлённые.
            // Вижу подмастерья (м.р.) - как одушевлённое.
			Ptr <const IPhrasePart> p = *part;
            tstring form = p->GetFormByCase (padeg, isAnimate && rodChislo != SingNeut);
            if (form.empty ()) return form;
            s += form;
        }

        return s;
	}

	tstring PaucalForm () const
	{
        tstring s;

		list <Ptr <const IPhrasePart> >::const_iterator singularIter = parts.begin ();
		list <Ptr <const IPhrasePart> >::const_iterator pluralIter;
		if (pluralPhrase) pluralIter = pluralPhrase->parts.begin ();

		for (; singularIter != parts.end (); ++singularIter)
        {
			Ptr <const IPhrasePart> p = (*singularIter)->PaulcalIsSingular () ? *singularIter : pluralPhrase ? *pluralIter : Ptr <const IPhrasePart> (0);
			if (!p) return _T("");

            tstring form = p->GetFormByCase (Padeg_R, isAnimate);
            if (form.empty ()) return form;

            s += form;

			if (pluralPhrase) ++pluralIter;
        }

        return s;
	}
};

typedef const std::tstring (Phrase::*PhraseMemFun) () const;

// набор падежных окончаний (парадигма склонения)
class Endings 
{
protected:
	~Endings() {}

public:

	virtual tstring operator [] (Padeg c) const = 0;

	// винительный одушевленного слова равен родительному
	virtual bool AccAnimEqualsGen () const = 0;

    // несклоняемое
    virtual bool IsNotInflected () const = 0;

	virtual EndingsType GetEndingsType () const = 0;
};

class GramInfo;

class Info
{
protected:
	~Info()
	{
	}

public:
    /// <summary>
    /// Возвращает NULL, если лемма этой статьи является словом в косвенном падеже.
    /// </summary>
	virtual const GramInfo * Nominative () const = 0; // C#: Именительный
};

class SingularOrPluralGramInfo
{
    const Endings * endings;
	bool fleetingVowel;
	bool endingStressed;

public:

	SingularOrPluralGramInfo () {} // Для создания массива в GramInfoFactory

    SingularOrPluralGramInfo (
            const Endings * endings,
		    bool fleetingVowel,
		    bool endingStressed
        )
		  : endings (endings)
		  , fleetingVowel (fleetingVowel)
		  , endingStressed (endingStressed)
    {
    }

    const Endings * GetEndings () const {return endings;}
	bool EndingStressed () const {return endingStressed;}
	bool FleetingVowel () const {return fleetingVowel;}
};

// Грамматическая информация о слове в именительном падеже.
class GramInfo : public Info
{
	SingularOrPluralGramInfo singular;
	SingularOrPluralGramInfo plural;

public:
	GramInfo () {} // Для создания массива в Dictionary.

	GramInfo (RodChislo rodChislo, bool IsNoun, 
		const ::Endings * endings, bool endingIsStressed, bool hasVolatileVowel,
		const ::Endings * endingsPlural, bool endingIsStressedPlural, bool hasVolatileVowelPlural,
		bool IsAnimate, bool Alteration, bool Na) 

		  : singular (endings, hasVolatileVowel, endingIsStressed)
		  , plural (endingsPlural, hasVolatileVowelPlural, endingIsStressedPlural)
		  , rodChislo (rodChislo)
		  , IsNoun (IsNoun)
		  , IsAnimate (IsAnimate)
		  , Alteration (Alteration)
		  , Na (Na)
	{}

	const SingularOrPluralGramInfo * GetSingular () const {return &singular;}
	const SingularOrPluralGramInfo * GetPlural () const {return &plural;}

	const Endings * GetEndings () const {return singular.GetEndings ();}
	const Endings * GetEndingsPlural () const {return plural.GetEndings ();}
	RodChislo rodChislo;
	bool IsNoun;
	bool EndingIsStressed () const {return singular.EndingStressed ();} // ending ударное
	bool EndingIsStressedPlural () const {return plural.EndingStressed ();} // ending ударное во множественном
	bool IsAnimate; // одушевлённое
	bool Alteration; // чередование
	bool Na; // местный с предлогом НА

	const GramInfo * Nominative () const { return this; } 
};

// конкретные парадигмы (данные для реализации интерфейса Endings)
namespace Paradigms 
{
    struct Row 
	{
        const TCHAR * endings [7];
        bool accAnimEqualsGen;
		EndingsType endingsType;
    };

	static Row 

		Uninflected   = {{ _T(""),     _T(""),    _T(""),  _T(""),   _T(""),    _T("")}, false, EndingsType_Uninflected},

		PronomialSingularFeminine     = {{ _T("а"),  _T("ой"),  _T("ой"), _T("у"),  _T("ой"), _T("ой")}, false, EndingsType_Pronomial},
		PronomialSingularMasculine    = {{ _T(""),  _T("ого"), _T("ому"),  _T(""),  _T("ым"), _T("ом")}, true,  EndingsType_Pronomial},
		PronomialSingularMasculineOV  = {{ _T(""),    _T("а"),   _T("у"),  _T(""),  _T("ым"), _T("ом")}, true,  EndingsType_Pronomial},
		PronomialSingularNeuter       = {{ _T("о"), _T("ого"), _T("ому"), _T("о"),  _T("ым"), _T("ом")}, true,  EndingsType_Pronomial},
		PronomialSingularNeuterOV     = {{ _T("о"),   _T("а"),   _T("у"), _T("о"),  _T("ым"), _T("ом")}, true,  EndingsType_Pronomial},
		PronomialPlural               = {{ _T("ы"),  _T("ых"),  _T("ым"), _T("ы"), _T("ыми"), _T("ых")}, true,  EndingsType_Pronomial},

		AdjectivalSingularFeminine                 = {{ _T("ая"), _T("ой"),  _T("ой"), _T("ую"), _T("ой"), _T("ой")}, false, EndingsType_Adjectival},
		AdjectivalSingularMasculineEndingStressed  = {{ _T("ой"),_T("ого"), _T("ому"), _T("ой"), _T("ым"), _T("ом")}, true,  EndingsType_Adjectival},
		AdjectivalSingularMasculineStemStressed    = {{ _T("ый"),_T("ого"), _T("ому"), _T("ый"), _T("ым"), _T("ом")}, true,  EndingsType_Adjectival},
		AdjectivalSingularNeuter                   = {{ _T("ое"),_T("ого"), _T("ому"), _T("ое"), _T("ым"), _T("ом")}, true,  EndingsType_Adjectival}, 
		AdjectivalPlural                           = {{ _T("ые"), _T("ых"),  _T("ым"), _T("ые"),_T("ыми"), _T("ых")}, true,  EndingsType_Adjectival}, 

		Noun1stDecl           = {{ _T("а"),   _T("ы"),   _T("е"), _T("у"),  _T("ой"),  _T("е")}, false, EndingsType_NounLike}, 
		Noun2ndDeclMasculine  = {{  _T(""),   _T("а"),   _T("у"),  _T(""),  _T("ом"),  _T("е")}, true,  EndingsType_NounLike}, 
		Noun2ndDeclMasculineU = {{  _T(""),   _T("а"),   _T("у"),  _T(""),  _T("ом"),  _T("е"),  _T("у")}, true, EndingsType_NounLike}, 
		Noun2ndDeclNeuter     = {{ _T("о"),   _T("а"),   _T("у"), _T("о"),  _T("ом"),  _T("е")}, true,  EndingsType_NounLike}, 
		Noun3rdDecl           = {{  _T(""),   _T("и"),   _T("и"),  _T(""),  _T("ью"),  _T("и")}, false, EndingsType_NounLike},

		MasculineFamilyName   = {{ _T(""),    _T("а"),   _T("у"),  _T(""),  _T("ым"),  _T("е")}, true, EndingsType_Pronomial};

	static Row * table [] = 
	{
		&Uninflected,
		&PronomialSingularFeminine,
		&PronomialSingularMasculine,
		&PronomialSingularMasculineOV,
		&PronomialSingularNeuter,
		&PronomialSingularNeuterOV,
		&PronomialPlural,
		&AdjectivalSingularFeminine,
		&AdjectivalSingularMasculineEndingStressed,
		&AdjectivalSingularMasculineStemStressed,
		&AdjectivalSingularNeuter,
		&AdjectivalPlural,
		&Noun1stDecl,  
		&Noun2ndDeclMasculine,
		&Noun2ndDeclMasculineU,
		&Noun2ndDeclNeuter,
		&Noun3rdDecl,
		&MasculineFamilyName

		// СущМнож - отдельный класс SubstPlural
	};
};

// парадигма без параметров,
// окончания выбираются из таблицы
class ParameterlessParadigm : public Endings 
{
public:
	Paradigms::Row * row; // TODO make private
	ParameterlessParadigm () {} // only for creating arrays, will be init'd with placement new.
	ParameterlessParadigm (Paradigms::Row * row) : row (row) {}
	tstring operator [] (Padeg c) const {return row->endings [c];}
	bool AccAnimEqualsGen () const {return row->accAnimEqualsGen;}
    bool IsNotInflected () const {return row == &Paradigms::Uninflected;}
	EndingsType GetEndingsType () const {return row->endingsType;}
};

class GramInfoFactory
{
	ParameterlessParadigm masculineFamilyNameEndings;
	SingularOrPluralGramInfo masculineFamilyName;
	vector <ParameterlessParadigm> singularEndings;

	void InitEndings ()
	{
		const size_t n = sizeof (Paradigms::table) / sizeof (*Paradigms::table);

		singularEndings.reserve (n);

		for (size_t i=0; i < n; ++i)
		{
			if (Paradigms::table [i]->endings [Padeg_M] == 0)
			{
				Paradigms::table [i]->endings [Padeg_M] = Paradigms::table [i]->endings [Padeg_P];
			}

			singularEndings.push_back (ParameterlessParadigm (Paradigms::table [i]));
		}
	}

	SingularOrPluralGramInfo gis [sizeof (Paradigms::table) / sizeof (*Paradigms::table)] [2];

	void InitGramInfos ()
	{
		size_t count = sizeof (gis) / sizeof (*gis);

		for (size_t i=0; i < count; ++i)
		{
			for (int j=0; j < 2; ++j)
			{
				gis [i][j] = SingularOrPluralGramInfo (&singularEndings [i], false, j != 0);
			}
		}
	}

	static int IndexOf (Paradigms::Row * row)
	{
		int count = sizeof (Paradigms::table) / sizeof (*Paradigms::table);

		for (int i=0; i < count; ++i)
		{
			if (Paradigms::table [i] == row) return i;
		}

		throw exception ();
	}

public:

	GramInfoFactory ()
		  : masculineFamilyNameEndings (&Paradigms::MasculineFamilyName),
			masculineFamilyName (&masculineFamilyNameEndings, false, false)
	{
		InitEndings ();

		InitGramInfos ();
	}

	const SingularOrPluralGramInfo * MasculineFamilyName () const
	{
		return &masculineFamilyName;
	}

	const SingularOrPluralGramInfo * GetGramInfo (Paradigms::Row * row, bool endingStressed) const
	{
		return & gis [IndexOf (row)] [endingStressed ? 1 : 0];
	}

	const vector <ParameterlessParadigm> & GetEndings () const
	{
		return singularEndings;
	}
};


class _Indirect : public Info // C#: _Косвенный
{
public:
	_Indirect () {}
	const GramInfo * Nominative () const { return 0; } 
};

static const _Indirect Indirect;



static size_t GetInt32 (stringstream & file)
{
	size_t entryCount = (unsigned char) file.get ();
	entryCount += ((size_t) (unsigned char) file.get ()) << 8;
	entryCount += ((size_t) (unsigned char) file.get ()) << 16;
	entryCount += ((size_t) (unsigned char) file.get ()) << 24;
	return entryCount;
}

class FioDictionary
{
	struct Entry
	{
		Entry (const tstring & lemma, FioPart fioPart)
			: Lemma (lemma)
			, fioPart (fioPart)
		{
		}

		tstring Lemma;
		FioPart fioPart;
	};

	struct EntryComparer
	{
		bool operator () (const Entry & ths, const Entry & that)
		{
			if (ths.Lemma != that.Lemma) return ths.Lemma < that.Lemma;

			return ths.fioPart < that.fioPart;
		}
	};

	typedef vector <Entry> Entries;
	Entries entries;

public:

	FioDictionary ()
	{
		stringstream file (string ((char * ) fioDict, fioDict_size));

		file.exceptions (file.exceptions () | ios::eofbit);

		LemmaReader lemmaReader (file, LemmaReader::CompareFromBeginning);

		size_t count = GetInt32 (file);

		entries.reserve (count);

		for (size_t i=0; i < count; ++i)
		{
			unsigned char b = 0;

			const tstring & lemma = lemmaReader.GetNext (&b);

			entries.push_back (Entry (lemma, (FioPart) b));
		}
	}

	class CompareByLemmas
	{
	public:
		bool operator () (const Entry & e1, const Entry & e2) const
		{
			return e1.Lemma.compare (e2.Lemma) < 0;
		}
	};

	bool Contains (const tstring & lemma) const
	{
		// Если здесь возникает Access violation, то скорее всего, передан неправильный ключ в функцию Unlock.

		Entries::const_iterator entry = lower_bound (entries.begin (), 
                                            entries.end (), 
                                            Entry (lemma, (FioPart) 0), 
                                            CompareByLemmas ());

		if (entry == entries.end ()) return false;

		return entry->Lemma == lemma;
	}

	bool Contains (const tstring & lemma, FioPart fioPart) const
	{
		Entries::const_iterator entry = lower_bound (entries.begin (), 
                                            entries.end (), 
                                            Entry (lemma, fioPart), EntryComparer ());

		if (entry == entries.end ()) return false;

		return entry->Lemma == lemma && entry->fioPart == fioPart;
	}
};

class Dictionary 
{
	// сравнение строк с конца
	class ReverseLess 
	{
		class CharLess 
		{
			unsigned char table [
#ifdef _UNICODE
				0x4FA // вмещает все кириллические буквы в Юникоде
#else
				256
#endif
			]; 

			size_t IndexOf (TCHAR c) const
			{
				// Функция IndexOf оптимизирована при помощи таблицы.
				return table [ToTableIndex (c)];
			}

		public:

			CharLess () 
			{
				// алфавит должен в точности совпадать 
				// с алфавитом в .NET версии
				const TCHAR alphabet [] = 
					_T("аоуэыияюёейгкхжшщчцбпвфдтзсмнлрьъ-");

				for (size_t i=0; i < sizeof (alphabet) / sizeof (*alphabet); ++i)
				{
					table [ToTableIndex (alphabet [i])] = (unsigned char) i;
				}
			}

			bool operator () (TCHAR c1, TCHAR c2) const {
				return IndexOf (c1) < IndexOf (c2);
			}

			unsigned ToTableIndex (TCHAR c) const
			{
				return
#ifdef _UNICODE
					(unsigned)
#else
					(unsigned char)
#endif
					c;
			}
		};

	public: 

		bool operator () (const tstring & s1, const tstring & s2) const 
		{
			static CharLess less;

			return lexicographical_compare (
				s1.rbegin (), s1.rend (),
				s2.rbegin (), s2.rend (), less);
		}
	};

	struct Entry
	{
		Entry (const tstring & lemma, const Info * gi)
			: Lemma (lemma)
			, GramInfo (gi)
		{
		}

		tstring Lemma;
		const Info * GramInfo;
	};

	typedef vector <Entry> Entries;
	Entries entries;

	GramInfoFactory gramInfoFactory;

	map <tstring, const GramInfo *> homonyms;

	class SubstPlural : public Endings {
	public:
		SubstPlural () {}
		SubstPlural (unsigned char nomEnding, unsigned char genEnding) 
			: nomEnding (nomEnding)
			, genEnding (genEnding)
		{
		}

	private:
		unsigned char nomEnding;
		unsigned char genEnding;

		tstring operator [] (Padeg c) const {
			static const TCHAR * endings [] = {0, 0, _T("ам"), 0, _T("ами"), _T("ах"), _T("ах")};
			static const TCHAR * nomStr [] = {_T("ы"), _T("а"), _T("е")};
			static const TCHAR * genStr [] = {_T(""), _T("ов"), _T("ей")};
			if (c == Padeg_I || c == Padeg_V) return nomStr [nomEnding];
			if (c == Padeg_R) return genStr [genEnding];
			return endings [c];
		}
		bool AccAnimEqualsGen () const {return true;}
        bool IsNotInflected () const {return false;}
		EndingsType GetEndingsType () const {return EndingsType_NounLike;}
	};

	SubstPlural pluralEndings [3][3];

	bool pluralEndingsInitialised;

	const Endings * DecodeGramInfoByte1 (unsigned char b) 
	{
		b &= 0x3F;

		if ((b & 1) == 0)
		{
			const vector <ParameterlessParadigm> & singularEndings = 
				gramInfoFactory.GetEndings ();

			b >>= 1;

			// -1 для MasculineFamilyName
			if (b >= singularEndings.size () - 1) throw exception ();

			return & singularEndings [b];
		} 
		else 
		{
			if (! pluralEndingsInitialised)
			{
				pluralEndingsInitialised = true;

				for (int i=0; i<3; ++i)
				for (int j=0; j<3; ++j)
				{
					new (& pluralEndings [i] [j]) SubstPlural (i, j);
				}
			}

			unsigned char nom = (b >> 1) & 3;
			unsigned char gen = (b >> 3) & 3;

			if (gen == 3) throw exception ();

			return &pluralEndings [nom] [gen];
		} 
	}

	vector <GramInfo> gis;

	static bool Less (const Entry & e1, const Entry & e2)
	{
		return ReverseLess () (e1.Lemma, e2.Lemma);
	}

public:

	Dictionary () : pluralEndingsInitialised (false)
	{
		stringstream file (string ((char * ) dict, dict_size));

		file.exceptions (file.exceptions () | ios::eofbit);

		int s0 = file.get ();
		int s1 = file.get ();
		size_t gisCount = s0 + (s1 << 8);

		gis.reserve (gisCount);

		for (size_t i=0; i < gisCount; ++i)
		{
			unsigned char b0 = file.get ();
			unsigned char b1 = file.get ();
			unsigned char b2 = file.get ();
			GramInfo gi (
				(RodChislo) (b0 & 3), // RodChislo 
				((b0 >> 2) & 1) != 0, // IsNoun
				DecodeGramInfoByte1 (b1), // Endings
				((b0 >> 3) & 1) != 0, // EndingIsStressed 
				((b0 >> 4) & 1) != 0, // HasVolatileVowel
				b2 == 255 ? 0 : DecodeGramInfoByte1 (b2), // EndingsPlural
				((b0 >> 6) & 1) != 0, // EndingIsStressedPlural 
				((b1 >> 6) & 1) != 0, // HasVolatileVowelPlural
				((b0 >> 5) & 1) != 0, // IsAnimate
				((b0 >> 7) & 1) != 0, // Alternation
				(b1 & 0x80) != 0); // Na

			gis.push_back (gi);
		}

		// прочитать количество статей
		size_t entryCount = GetInt32 (file);

		entries.reserve (entryCount);

		LemmaReader lemmaReader (file, LemmaReader::CompareFromEnd);

		for (size_t i=0; i < entryCount; ++i) // цикл по статьям
		{
			unsigned char c = 0;

			const tstring & lemma = lemmaReader.GetNext (&c);

			// прочитать грамматическую информацию
			size_t gii = (unsigned char) file.get () + (c << 8);

			if (gii > gis.size ()) throw exception ();

			const Info * gi = (gii == gis.size ()) ? (const Info *) &Indirect : & gis [gii];

			// создать статью
			entries.push_back (Entry (lemma, gi));
		}

        // Прочитать homonyms.

		size_t count = GetInt32 (file);

        LemmaReader lemmaReader2 (file, LemmaReader::CompareFromBeginning);

        for (size_t i=0; i < count; ++i)
        {
            unsigned char b = 0;

            const tstring & lemma = lemmaReader2.GetNext (&b);

            homonyms [lemma] = & gis [file.get () + (b << 8)];
        }

		// инициализировать несклоняемые
		for (int rc = 0; rc < RodChislo_Count; ++rc)
		{
			for (int n=0; n < 2; ++n)
			{
				const Endings * endings = & gramInfoFactory.GetEndings () [0];

				uninflected [rc] [n] = GramInfo ((RodChislo) rc, 
					n==0, // IsNoun 
					endings, false, false,
					endings, false, false,
					false, false, false);
			}
		}
	}

	const GramInfoFactory & getGramInfoFactory () const {return gramInfoFactory;}

    const GramInfo * FindHomonym (const tstring & lemma) const
    {
		map <tstring, const GramInfo *>::const_iterator i = homonyms.find (lemma);

		if (i == homonyms.end ()) return 0;

        return i->second;
    }

	typedef list <const GramInfo *> ResultSet;

	void Find (const tstring & lemma, bool first, ResultSet & result) const 
	{
		Entries::const_iterator entry0 = upper_bound (entries.begin (), entries.end (), Entry (lemma, 0), Less);

		// entry0 не может быть равно entries.begin (), т.к. первое слово в словаре "а" меньше любого другого.
		// а upper_bound выдает первый элемент, БОЛЬШИЙ данного.
		--entry0; 

		if (entry0->Lemma != lemma) 
		{
            if (first)
            {
                if (entry0->GramInfo->Nominative () == 0)
                {
                    // например "лений"
                    // продолжаем искать варианты склонения первого слова

					Entries::const_iterator j = entry0;
					do
                    {
						--j;

                        if (j->GramInfo->Nominative () != 0)
                        {
                            entry0 = j;
                            break;
                        }
                    }
					while (j != entries.begin ());
                }
            }

	        AddResult (result, entry0, lemma);
        } 
		else 
		{
            // Cлово полностью совпадает со словарной статьёй.
            // Если есть другие статьи с такой же леммой, добавить их тоже.

			// Мы уже уменьшили entry0 на 1, поэтому сейчас она указывает на последний из равных.

            while (entry0 != entries.begin ())
			{
				Entries::const_iterator entry1 = entry0 - 1;
                if (entry1->Lemma != lemma) break;
				entry0 = entry1;
			}

			// entry0 теперь указывает на первый из равных

            // Порядок добавления результатов важен, т.к. с помощью изменения порядка слов в zalizniak.txt
            // мы можем контролировать, какой из омографов имеет приоритет, когда он употреблен без контекста
            // (например, моль - ж.р. имеет приоритет перед ж.р.)
            for (; entry0 != entries.end () && entry0->Lemma == lemma; ++entry0)
			{
                if (entry0->GramInfo->Nominative () == 0) continue;

				AddResult (result, entry0, lemma);
            }

            if (first && result.empty ())
            {
                for (; entry0 != entries.begin (); --entry0)
                {
	                if (entry0->GramInfo->Nominative () == 0) continue;
					AddResult (result, entry0, lemma);
                    break;
                }
            }
        }
	}

	bool ContainsFullLemma (const tstring & lemma) const
	{
		Entries::const_iterator entry0 = lower_bound (entries.begin (), entries.end (), Entry (lemma, 0), Less);

		return entry0 != entries.end () && entry0->Lemma.compare (lemma) == 0;
	}

	bool CanBeGenitive (const tstring & lemma) const
	{
		Entries::const_iterator entry0 = lower_bound (entries.begin (), entries.end (), Entry (lemma, 0), Less);

        if (entry0 == entries.end () || entry0->Lemma != lemma) 
        {
            --entry0;

            return CanBeGenitive (entry0);
        }

        // Cлово полностью совпадает со словарной статьёй.

        for (; entry0 != entries.end (); ++entry0) 
		{
            if (entry0->Lemma != lemma) break;
            if (CanBeGenitive (entry0)) return true;
        }

        return false;
	}

	Gender GetGender (const tstring & lemma) const
	{
		ResultSet result;

		Find (lemma, true, result);

		bool masculine = false;
		bool feminine = false;

		for (ResultSet::const_iterator gi = result.begin (); gi != result.end (); ++gi)
		{
			switch ((*gi)->rodChislo)
			{
				case SingMasc: masculine = true; break;
				case SingFem:  feminine = true; break;
				case SingNeut:
				case Plural:
				case RodChislo_Count:
					break;
			}
		}

        if (masculine && feminine) return Gender_Unisex;
        if (masculine) return Gender_Masculine;
        if (feminine) return Gender_Feminine;

        return Gender_Unisex;
	}

	const GramInfo * Uninflected (RodChislo rodChislo, bool isNoun) const
	{
		return & uninflected [(int) rodChislo] [isNoun ? 0 : 1];
	}

private:

	GramInfo uninflected [RodChislo_Count] [2];

	static bool CanBeGenitive (Entries::const_iterator entry)
	{
        const GramInfo * gi = entry->GramInfo->Nominative ();
        if (gi == 0) return true;
        if (gi->GetEndings ()->GetEndingsType () == EndingsType_Adjectival && EndsWith (entry->Lemma, _T("ой"))) return true; // молодой
        if (gi->rodChislo == Plural && EndsWithOneOf (entry->Lemma, _T("и,ы,а"))) return true;
        return false;
	}

    void AddResult (
        ResultSet & results, 
        Entries::const_iterator result,
        const tstring & lemma) const
    {
        const GramInfo * gramInfo = result->GramInfo->Nominative ();

		if (!gramInfo) return;

        // Если лемма не оканчивается на окончание им.п.,
        // то этот вариант не подходит.
        // Эта проверка введена после найденного Володей
        // Аверкиным бага в слове "Сергея", которое неправильно
        // склонялось по адъективному типу.
        if (HasEnding (lemma, (*gramInfo->GetEndings ()) [Padeg_I]))
		{
			results.push_back (gramInfo);
		}
		else
		{
            // например, "ие"
            // Признак "Сущ" ставим false, чтобы нормально склонялось "ответственный за"
			results.push_back (Uninflected (SingMasc, false));
			results.push_back (Uninflected (SingFem,  false));
			results.push_back (Uninflected (SingNeut, false));
			results.push_back (Uninflected (Plural,   false));
		}
    }
};

class IMorpher 
{
public:
	virtual ~IMorpher() {}

	virtual Phrase * Analyse (const tstring & s, Attributes attributes) const = 0;
};

class RussianDeclensor : public IMorpher // C#: Склонятель
{
	Dictionary dictionary;
	FioDictionary fioDict;

	static void Tokenize (const tstring & s, list <DecoratedWord> & decoratedWords, list <tstring> & separators)
	{
        Tokenizer t = Tokenizer (s, Tokenizer::Russian);

        for (;;)
        {
            separators.push_back (t.GetNextSeparator ());

            tstring word = t.GetNextToken ();

            if (word.empty ()) break;

            Tokenizer::ChangeLatinToCyrillic (&word);

            DecoratedWord decoratedWord = DecoratedWord::Get (word);

            decoratedWords.push_back (decoratedWord);
        }
	}

	static void RemoveNonRussian (list <DecoratedWord> & decoratedWords, list <tstring> & separators)
	{
		list <DecoratedWord>::iterator word = decoratedWords.begin ();
		list <tstring>::iterator separator = separators.begin ();

		while (word != decoratedWords.end ())
		{
			if (Tokenizer::IsRussian (word->Lemma ()))
			{
				++word;
				++separator;
			}
			else 
			{
				list <DecoratedWord>::iterator wordToRemove = word;
				++word;
				tstring wordToRemoveLemma = wordToRemove->DecoratedLemma ();
				decoratedWords.erase (wordToRemove);

				list <tstring>::iterator nextSep = separator;
				++nextSep;
				*separator += wordToRemoveLemma + *nextSep;
				separators.erase (nextSep);
			}
		}
	}

	static bool IsHyphen (const tstring & s)
	{
        return (s == _T("-") || s == _T("–"));
	}

	// C#: ОбъединитьСловаСДефисом
	static void JoinHypenatedWords (
		list <DecoratedWord> & decoratedWords, 
		list <tstring> & separators,
		list <MultiPartDecoratedWord> & result)
	{
        list <DecoratedWord>::iterator wordNode = decoratedWords.begin ();
        list <tstring>::iterator separatorNode = separators.begin ();

        for (; wordNode != decoratedWords.end (); ++wordNode)
        {
            ++separatorNode;

            vector <DecoratedWord> parts;

            parts.push_back (*wordNode);

            while (++wordNode != decoratedWords.end () && IsHyphen (*separatorNode))
            {
                parts.push_back (*wordNode);
                ++separatorNode;
            }

			--wordNode;

            result.push_back (MultiPartDecoratedWord (parts));
        }
	}

    template <class T> static void Move (list <T> & to, list <T> & from)
    {
		assert (from.size () > 0);

		to.splice (to.end (), from, from.begin (), ++from.begin ());
    }

    /// <summary>
    /// Перенести столько разделителей, сколько частей в слове.
    /// </summary>
    static void Move (list <tstring> & currentDelimList, list <tstring> & separators, const list <MultiPartDecoratedWord> & currentWordList)
    {
        for (size_t i=0; i < currentWordList.rbegin ()->PartCount (); ++i)
        {
            Move (currentDelimList, separators);
        }
    }

	static tstring Trim (const tstring & s)
	{
		const TCHAR * ws = _T(" .\t\n\r");

		size_t first = s.find_first_not_of (ws);
		if (first == tstring::npos) return _T("");

		size_t last = s.find_last_not_of (ws, first);
		if (last == tstring::npos) return _T("");

		return s.substr (first, last - first + 1);
	}

    static bool IsDash (const tstring & s)
    {
		tstring p = Trim (s);

		if (p == _T("-"))  return true;
		if (p == _T("--")) return true;
		if (p == _T("–"))  return true;
		if (p == _T("—"))  return true;

		return false;
    }

    static bool IsInitial (const tstring & s)
    {
        return s.length () == 1 || ! Common::ContainsVowel (s);
    }

    static bool IsInitial (const MultiPartDecoratedWord & multiPartDecoratedWord)
    {
        for (vector <DecoratedWord>::const_iterator part = multiPartDecoratedWord.Parts ().begin (); part != multiPartDecoratedWord.Parts ().end (); ++part)
        {
            if (! IsInitial (part->Lemma ())) return false;
        }

        return true;
    }

    static void HandleInitials (list <MultiPartDecoratedWord> & components, list <tstring> & separators)
    {
        list <MultiPartDecoratedWord>::iterator component = components.begin ();
        list <tstring>::iterator separator = separators.begin ();

        while (component != components.end ())
        {
            if (IsInitial (*component))
            {
                tstring s = *separator;

				for (vector <DecoratedWord>::const_iterator word = component->Parts ().begin (); word != component->Parts ().end (); ++word)
                {
					list <tstring>::iterator previous = separator;
                    ++separator;
                    separators.erase (previous);
                    s += word->DecoratedLemma () + *separator;
                }
                *separator = s;
                list <MultiPartDecoratedWord>::iterator componentToRemove = component;
                ++component;
                components.erase (componentToRemove);
            }
            else 
            {
                ++component;
                ++separator;
            }
        }
    }

    bool NotFoundInDictionaryOrInfectionIsAmbiguous (const tstring & component) const
    {
        bool foundInDictionary = fioDict.Contains (component);
        Gender genderAsPerDictionary = dictionary.GetGender (component);
        bool endsInConsonant = Common::IsConsonant (*component.rbegin ());

        bool result = ! foundInDictionary || (genderAsPerDictionary == Gender_Feminine && endsInConsonant);

        return result;
    }

	const GramInfo * Uninflected (RodChislo rodChislo, bool isNoun) const
	{
		return dictionary.Uninflected (rodChislo, isNoun);
	}

    string ChooseCombination (const list <MultiPartDecoratedWord> & components, RodChislo * rodChisloResult, Attributes atts) const
    {
        // HACK для "батоно Пак Гульфияр..."
        if (components.size () > 3)
        {
            *rodChisloResult = SingMasc;
            return string (components.size (), 'I');
        }

		int both = Attribute_Masc | Attribute_Fem;

		// Если ни одного рода не указано, это равносильно тому, что указаны оба,
		// т.е. род определяется автоматически.
		// В .NET версии это не так - указание обоих приводит к исключению.
		if ((atts & both) == 0) atts = (Attributes) (atts | both);

        if (components.size () == 2)
        {
            const char * combinations [] = {"FI", "IF"};

			vector <MultiPartDecoratedWord> sost (components.begin (), components.end ());

            for (int i=0; i<2; ++i)
            {
                const tstring & familyName = sost [i].LastPart ();
                const tstring & givenName = sost [1-i].LastPart ();

                // Патч для "Герцен Элла" - обе составляющие есть в словаре как имена, и только одно есть как familyName (Герцен).
                if (fioDict.Contains (familyName, FioPart_GivenName) && fioDict.Contains (givenName, FioPart_GivenName) &&
                    fioDict.Contains (familyName, FioPart_FamilyName) && !fioDict.Contains (givenName, FioPart_FamilyName))
                {
                    switch (dictionary.GetGender (givenName))
                    {
                        case Gender_Masculine:
                            *rodChisloResult = SingMasc;
                            break;
                        case Gender_Feminine:
                            *rodChisloResult = SingFem;
                            break;
                        case Gender_Unisex:
                            continue;
                        default:
                            throw exception (); // "Неправильный Gender."
                    }
                    return combinations [i];
                }

                // патч для "Асадов Наргиз", "Иванов Женя"
                if (NotFoundInDictionaryOrInfectionIsAmbiguous (familyName) && 
                    NotFoundInDictionaryOrInfectionIsAmbiguous (givenName))
                {
                    if (HasEndings (familyName, _T("ов,ын,ой,ый,ович")))
                    {
                        *rodChisloResult = SingMasc;
                        return combinations [i];
                    }
                    if (HasEndings (familyName, _T("ова,ына,ая,овна")))
                    {
                        *rodChisloResult = SingFem;
                        return combinations [i];
                    }
                    if (EndsWithOneOf (familyName, _T("дзе,ян,енко")))
                    {
                        *rodChisloResult = ConvertGenderToRodChislo (dictionary.GetGender (givenName));
                        return combinations [i];
                    }
                }
            }
        }

        {
            string bestCombination = "";
            RodChislo bestRodChislo = SingMasc;

            int bestScore = -1;

			string combinations [] = {"FIO","IOF","IF","FI","IO","F","I","O"};

			for (size_t k=0; k < sizeof (combinations) / sizeof (*combinations); ++k)
            {
				const string & combination = combinations [k];

                if (components.size () != combination.length ()) continue;

				RodChislo rodChislo = SingMasc;

				for (int j=0; j < 2; ++j, rodChislo = Opposite (rodChislo))
                {
					Attributes rodattr = (rodChislo == SingMasc) ? Attribute_Masc : Attribute_Fem;

					if ((atts & rodattr) == 0) 
						continue;

                    int score = 0;

					unsigned i = 0; 

                    for (list <MultiPartDecoratedWord>::const_iterator component = components.begin ();
						i < components.size (); 
						++component, ++i)
                    {
                        FioPart fioPart = GetFioPart (combination [i]);

                        const tstring & lastPart = component->LastPart ();

                        if (fioDict.Contains (component->Lemma (), fioPart))
                        {
                            if (GenderMatchesRodChislo (lastPart, rodChislo, fioPart))
                            {
                                // Именам дается приоритет по сравнению с фамилиями.
                                // "Айрих Любовь", "Галина Рерих" и т.п. - выигрывает имя.
                                score += fioPart == FioPart_FamilyName ? 2 : 3;
                            }
                            if ((atts & both) == both && GenderMatchesRodChislo (lastPart, Opposite (rodChislo), fioPart))
                            {
                                score -= 1;
                            }
                        }
                        else
                        {
                            FioPartAndGender partAndGender = GetfioPartAndGender (*component);

                            if (partAndGender.fioPart == fioPart && Matches (partAndGender.gender, rodChislo))
                            {
                                score += 1;
                            }
                        }
                    }

					if (bestScore < score)
                    {
                        bestScore = score;
                        bestCombination = combination;
                        bestRodChislo = rodChislo;
                    }
                }
            }

            *rodChisloResult = bestRodChislo;
            return bestCombination;
        }
    }

    static RodChislo ConvertGenderToRodChislo (Gender rodChislo)
    {
        switch (rodChislo)
        {
            case Gender_Masculine: return SingMasc;
            case Gender_Feminine: return SingFem;
            case Gender_Unisex: return SingMasc;
        }

        throw exception (); // "Неправильный Gender."
    }

    static bool Matches (Gender gender, RodChislo rodChislo)
    {
        if (gender == Gender_Unisex) return true;

        if (gender == Gender_Masculine && rodChislo == SingMasc) return true;

        if (gender == Gender_Feminine && rodChislo == SingFem) return true;

        return false;
    }

    bool GenderMatchesRodChislo (const tstring & lemma, RodChislo rodChislo, FioPart fioPart) const
    {
        if (lemma == _T("лука")) return rodChislo == SingMasc;

        return FindInCommonDictionary (lemma, rodChislo, fioPart) != 0;
    }

    struct FioPartAndGender
    {
        FioPart fioPart;
        Gender gender;

		FioPartAndGender (FioPart fioPart, Gender gender)
			: fioPart (fioPart)
			, gender (gender)
		{
		}
    };

    static FioPartAndGender GetFioPartAndGenderNullable (const tstring & word)
    {
		for (size_t i=0; i < sizeof (suffixes) / sizeof (*suffixes); ++i)
        {
            if (HasEndings (word, suffixes [i].Morphs)) 
            {
                return FioPartAndGender (suffixes [i].fioPart, suffixes [i].gender);
            }
        }

        return FioPartAndGender ((FioPart) -1, Gender_Feminine);
    }

    FioPartAndGender GetfioPartAndGender (const MultiPartDecoratedWord & word) const
    {
		for (vector <DecoratedWord>::const_iterator part = word.Parts ().begin (); part != word.Parts ().end (); ++part)
        {
            FioPartAndGender fioPartAndGender = GetFioPartAndGenderNullable (part->Lemma ());
                
            if (fioPartAndGender.fioPart != (FioPart) -1) return fioPartAndGender;
        }

        return FioPartAndGender (FioPart_GivenName, dictionary.GetGender (word.LastPart ()));
    }

    /// <summary>
    /// Возвращает NULL, если <paramref name="lemma"/> попадает на несклоняемое или plural/средний род.
    /// </summary>
    const SingularOrPluralGramInfo * FindInCommonDictionary (const tstring & lemma, RodChislo rodChislo, FioPart fioPart) const
    {
        Dictionary::ResultSet gramInfos;
		dictionary.Find (lemma, true, gramInfos);

        if (gramInfos.empty ()) return NULL;
            
        // фамилии, совпадающие с нарицательными 3-го склонения, не склоняются (Мышь, Грусть)
        if (fioPart == FioPart_FamilyName && gramInfos.size () == 1 && (*(*gramInfos.begin ())->GetEndings ()) [Padeg_T] == _T("ью"))
        {
            return Uninflected (rodChislo, true)->GetSingular ();
        }

        const GramInfo * gramInfo = 0;

		for (Dictionary::ResultSet::const_iterator gi = gramInfos.begin (); gi != gramInfos.end (); ++gi)
        {
            if ((*gi)->rodChislo == rodChislo)
            {
                gramInfo = *gi;
                break;
            }
        }

        if (gramInfo)
        {
            if (gramInfo->GetEndings ()->GetEndingsType () == EndingsType_Pronomial)
            {
                // Местоимённые собственные - это фамилии (Иванов), 
                // в т.ч. польские типа Стефаньска, Покорны.
                if (EndsWithOneOf (lemma, _T("и,ы,а")) || Common::HasPronomialFamilyNameSuffix (lemma))
                {
					return gramInfoFactory().GetGramInfo (rodChislo == SingMasc ?
						&Paradigms::MasculineFamilyName : &Paradigms::PronomialSingularFeminine, gramInfo->EndingIsStressed ());
                }
                else
                {
                    // Просто совпало с местоимённым нарицательным.  Делаем его субстантивным.
                    // Бабий, Чебан
					return gramInfoFactory().GetGramInfo (&Paradigms::Noun2ndDeclMasculine, false);
                }
            }
        }

        return gramInfo == NULL ? NULL : gramInfo->GetSingular ();
    }

	const GramInfoFactory & gramInfoFactory () const {return dictionary.getGramInfoFactory ();}

    static RodChislo Opposite (RodChislo rodChislo)
    {
        return (RodChislo) ((int) SingMasc + (int) SingFem - (int) rodChislo);
    }

    static FioPart GetFioPart (char c)
    {
        switch (c)
        {
            case 'F': return FioPart_FamilyName;
            case 'I': return FioPart_GivenName;
            case 'O': return FioPart_Patronymic;
        }

        throw exception ();
    }

    class ParsedPartFIO : public IParsedPart
    {
        const MultiPartDecoratedWord multiPartDecoratedWord;
        vector <const SingularOrPluralGramInfo *> gramInfo;

	public:
		ParsedPartFIO (
            const MultiPartDecoratedWord & multiPartDecoratedWord,
            const vector <const SingularOrPluralGramInfo *> & gramInfo
            )
			: multiPartDecoratedWord (multiPartDecoratedWord)
			, gramInfo (gramInfo)
        {
        }

		virtual bool AppendTo (PhraseBuilder & phraseBuilder, list <tstring>::const_iterator & separator, bool plural) const
		{
			if (plural) return false;

            for (size_t part=0; part < multiPartDecoratedWord.PartCount (); ++part)
            {
                if (part != 0) 
                {
                    phraseBuilder.Add (*separator++);
                }

				const ParameterlessParadigm & se = * (const ParameterlessParadigm *) gramInfo [part]->GetEndings ();
				tstring t = se [Padeg_T];
				tstring p = se [Padeg_P];

                phraseBuilder.Add  (new SingleWordWithGramInfo (
                    gramInfo [part], multiPartDecoratedWord.Parts () [part]));
            }

			return true;
        }

		virtual Ptr <IParsedPart> GetPlural () const
        {
            return 0;
        }
    };

    void AnalyseFio (
        list <MultiPartDecoratedWord> & components, 
        list <tstring> & separators,
        Attributes attributes,
        RodChislo * rodChislo,
        bool * isAnimate,
        bool * prepositionNA,
		list <Ptr <IParsedPart> > & result) const
	{
        *prepositionNA = false;

		*isAnimate = true;

        HandleInitials (components, separators);

        string combination = ChooseCombination (components, rodChislo, attributes);

		list <MultiPartDecoratedWord>::const_iterator component = components.begin ();

        for (size_t i=0; i < components.size (); ++i, ++component)
        {
			vector <const SingularOrPluralGramInfo *> gi;

            ParseWord (*component, *rodChislo, GetFioPart (combination [i]), gi);

			result.push_back (new ParsedPartFIO (*component, gi));
        }
	}

    void ParseWord (const MultiPartDecoratedWord & word, RodChislo rodChislo, FioPart fioPart, vector <const SingularOrPluralGramInfo *> & gi) const
    {
		gi.reserve (word.PartCount ());

        bool onlyLastPartIsInflected = OnlyLastPartIsInflected (word, fioPart)
            || fioDict.Contains (word.Lemma ());

        for (vector <DecoratedWord>::const_iterator part = word.Parts ().begin (); part != word.Parts ().end (); ++part)
        {
            bool isUninflected = onlyLastPartIsInflected && (gi.size () < word.PartCount () - 1);

            gi.push_back (isUninflected
                ? Uninflected (rodChislo, false)->GetSingular ()
                : ParseWord (part->Lemma (), rodChislo, fioPart));
        }
    }

    const SingularOrPluralGramInfo * ParseWord (const tstring & lemma, RodChislo rodChislo, FioPart fioPart) const
    {
        const SingularOrPluralGramInfo * gramInfo = GetGramInfo (lemma, fioPart, rodChislo);

        if (gramInfo) return gramInfo;

        Paradigms::Row * endings = GetEndingsAuto (lemma, rodChislo == SingMasc, fioPart);

        if (endings == NULL)
        {
            // NULL возвращается ТОЛЬКО для "нестандартных" фамилий (Лев).  Просклонять ее как имя.
            return ParseWord (lemma, rodChislo, FioPart_GivenName);
        }

		return gramInfoFactory ().GetGramInfo (endings, 
            endings->endingsType == EndingsType_Adjectival && tstring (_T("ой")).compare (endings->endings [Padeg_I]) == 0);
    }

    static Paradigms::Row * GetEndingsAuto (const tstring & word, bool masculine, FioPart fioPart)
    {
        return (fioPart == FioPart_FamilyName)
            ? GetFamilyNameEndings (word, masculine)
            : GetGivenNameEndings (word, masculine);
    }

    static Paradigms::Row * GetFamilyNameEndings (const tstring & familyName, bool masculine)
    {
        if (! masculine && HasEndings (familyName, _T("ая")))
        {
            // Большинство слов на -АЯ адъективного склонения (Лучинская, 
            // Осадчая), но бывают и субстантивного (типа: Ая, Рая, Тая, Ашая, Вирсая).

            // Имён на -ЯЯ я не нашёл, а фамилий очень мало: Заболотняя, Пидсадняя.

            return &Paradigms::AdjectivalSingularFeminine;
        }
        else if (masculine && HasEndings (familyName, _T("ой")))
        {
            return &Paradigms::AdjectivalSingularMasculineEndingStressed;
        }
        else if (masculine && HasEndings (familyName, _T("ый")))
        {
            return &Paradigms::AdjectivalSingularMasculineStemStressed;
        }
        else if (masculine && HasEndings (familyName, _T("ов,ин,цын"))) // Арын Болат Айдарулы
        {
            return &Paradigms::MasculineFamilyName;
        }
        else if (!masculine && HasEndings (familyName, _T("ова,ина,цына")))
        {
            return &Paradigms::PronomialSingularFeminine;
        }
        else if (HasEndings (familyName, _T("ых")))
        {
            return &Paradigms::Uninflected;
        }
        else 
        {
            return NULL;
        }
    }

    static Paradigms::Row * GetGivenNameEndings (const tstring & givenName, bool masculine)
    {
        if (HasEndings (givenName, _T("а")))
        {
            return &Paradigms::Noun1stDecl;
        }
        else if (Common::IsVowel (*givenName.rbegin ()))
        {
            return &Paradigms::Uninflected;
        }
        else
        {
            // На согласную, Ь или Ъ.
            if (masculine)
            {
                return &Paradigms::Noun2ndDeclMasculine;
            }
            else
            {
                return &Paradigms::Uninflected;
            }
        }
    }

    /// <summary>
    /// Возвращает 0, если <paramref name="lemma"/> попадает на несклоняемое или plural/средний род
    /// или если леммы нет в словаре ФИО и она имеет suffixes фамилии или отчества.
    /// </summary>
    const SingularOrPluralGramInfo * GetGramInfo (const tstring & lemma, FioPart fioPart, RodChislo rodChislo) const
    {
        // Женские фамилии на согласную не склоняются.
        if (rodChislo == SingFem && fioPart != FioPart_GivenName && !EndsWithOneOf (lemma, _T("а,я")))
        {
            return Uninflected (SingFem, true)->GetSingular ();
        }

		// TODO
        //// Омонимичны топонимам и фильтруются таблицей Омонимы в ФИО.mdb.
        //if (lemma == "дания") return new GramInfo (Парадигмы.СтандартныеОкончания.Noun1stDecl, RodChislo.SingFem, true /* ударное */, false /*беглая*/);
        //if (lemma == "алтай" && rodChislo == RodChislo.SingFem) return new GramInfo (Парадигмы.СтандартныеОкончания.Uninflected, RodChislo.SingFem, true, false);

        if (fioPart == FioPart_FamilyName && !fioDict.Contains (lemma, FioPart_FamilyName) && Common::HasFamilyNameSuffix (lemma))
        {
            return 0;
        }

        const GramInfo * gi = dictionary.FindHomonym (lemma);

        if (gi) 
        {
            // Фамилии мужского имеют тот же Byte, что и притяжательные отцов, дедов.
            if (gi->GetEndings ()->GetEndingsType () == EndingsType_Pronomial && gi->rodChislo == SingMasc)
            {
                // исправить окончание предложного.
                return gramInfoFactory ().MasculineFamilyName ();
            }

            return gi->GetSingular ();
        }

        return FindInCommonDictionary (lemma, rodChislo, fioPart);
    }

    bool OnlyLastPartIsInflected (const MultiPartDecoratedWord & word, FioPart fioPart) const
    {
		const vector <DecoratedWord> & parts = word.Parts ();

        switch (fioPart)
        {
            case FioPart_FamilyName: 
                return false;

            case FioPart_GivenName:
                for (size_t i=0; i < parts.size () - 1; ++i)
                {
                    if (fioDict.Contains (parts [i].Lemma (), FioPart_GivenName)) return false;
                }
                return true;

            case FioPart_Patronymic:

                // В "Саид-Ахметович" Саид не склоняется, но в "Игоревич-Олегович" склоняются обе parts.
                for (size_t i=0; i < parts.size () - 1; ++i)
                {
                    if (HasEndings (parts [i].Lemma (), _T("ыч,на"))) return false;
                }
                return true;
        }

        throw exception (); // "Invalid fioPart"
    }

	bool IsSpecialWord (const MultiPartDecoratedWord & word) const
	{
		// TODO
		return false;
	}

	bool IsCommonNoun (const tstring & lemma) const
	{
        return Common::HasFamilyNameSuffix (lemma) 
            && dictionary.ContainsFullLemma (lemma);
	}

	bool NotAHomonym (const tstring & lemma) const
	{
		// TODO
        return dictionary.FindHomonym (lemma) == 0;
	}

	bool IsCommonNoun (const list <MultiPartDecoratedWord> & components) const
	{
        return components.size () == 1 
            && IsCommonNoun ((*components.begin ()).LastPart ()) 
            && NotAHomonym ((*components.begin ()).LastPart ());
	}

    bool IsCommonNounWithFamilyNameSuffix (const tstring & lemma) const
    {
        return Common::HasFamilyNameSuffix (lemma) 
            && dictionary.ContainsFullLemma (lemma);
    }

	bool ContainsWordsFromFioDictionary (const list <MultiPartDecoratedWord> & components) const
	{
		list <MultiPartDecoratedWord>::const_iterator component = components.begin ();

        for (int i=0; i < 3 && component != components.end (); ++i, ++component)
        {
            tstring lowerCaseWord = component->Lemma ();

            if (IsCommonNounWithFamilyNameSuffix (lowerCaseWord)) 
            {
                if ((*component->Parts ().begin ()).GetCaps ().IsFirstCap ()) return true;
                continue;
            }

            if (fioDict.Contains (lowerCaseWord) && NotAHomonym (lowerCaseWord)) return true;
        }

        return false;
	}

	bool AllWordsLowercase (const list <MultiPartDecoratedWord> & components) const
	{
		for (list <MultiPartDecoratedWord>::const_iterator word = components.begin (); word != components.end (); ++word)
		{
			if (! word->IsSameCaps (Caps::AllLower)) return false;
		}

        return true;
	}

    static bool WordLikeIbn (const MultiPartDecoratedWord & word)
    {
		if (word.PartCount () != 1) return false;

		const tstring & s = word.LastPart ();

		if (s.compare (_T("ибн")) == 0) return true;
		if (s.compare (_T("ди")) == 0) return true;
		if (s.compare (_T("сан")) == 0) return true;
		if (s.compare (_T("батоно")) == 0) return true;

        return false;
    }

    static bool IsFirstCap (const MultiPartDecoratedWord & word)
    {
        return word.IsSameCaps (Caps::FirstCap) ||
			(word.FirstPart ().length () == 1 && word.IsSameCaps (Caps::AllCaps)); // IsInitial
    }

	static bool AllWordsTitleCase (const list <MultiPartDecoratedWord> & components)
	{
		for (list <MultiPartDecoratedWord>::const_iterator word = components.begin (); word != components.end (); ++word)
		{
            if (WordLikeIbn (*word)) continue;

            if (! (IsFirstCap (*word) || (word == components.begin () && components.size () > 1 && word->IsSameCaps (Caps::AllCaps)))) return false;
        }

        return true;
	}

	static bool CaseSuggestsName (const list <MultiPartDecoratedWord> & components)
	{
		return AllWordsInCaps (components) || AllWordsTitleCase (components);
	}

	static bool AllWordsInCaps (const list <MultiPartDecoratedWord> & components)
	{
		for (list <MultiPartDecoratedWord>::const_iterator word = components.begin (); word != components.end (); ++word)
		{
            if (WordLikeIbn (*word)) continue;

            if (! (word->IsSameCaps (Caps::AllCaps))) return false;
        }

        return true;
	}

    static bool EndsInOvoEvo (const list <MultiPartDecoratedWord> & components)
    {
        return AllWordsTitleCase (components) && 
            EndsWithOneOf ((*components.begin ()).LastPart (), _T("ово,ево,ёво,ино,ыно"));
    }

    static bool ContainsPatronymic (const list <MultiPartDecoratedWord> & components)
    {
		list <MultiPartDecoratedWord>::const_iterator word = ++components.begin ();

		for (int i=1; word != components.end () && i < 3; ++word, ++i)
        {
            tstring lowerCaseWord = word->LastPart ();

            if (EndsWithOneOf (lowerCaseWord, _T("ович,евич,овна,евна,инична,ыч,оглы,кызы")))
            {
                return true;
            }
        }

        return false;
    }

    static bool DoesNotLookLikeFio (const list <MultiPartDecoratedWord> & components)
    {
		for (list <MultiPartDecoratedWord>::const_iterator word = components.begin (); word != components.end (); ++word)
		{
            // "Соединённое Королевство, Агентство, Ойло Союзное, Подмосковье, Мытищи"
            if (EndsWithOneOf (word->LastPart (), _T("ое,ство,овье,щи"))) return true;
        }
            
        return false; 
    }

    bool HasFamilyNameSuffix (const tstring & lowerCaseWord) const
    {
        return Common::HasFamilyNameSuffix (lowerCaseWord) && ! IsCommonNounWithFamilyNameSuffix (lowerCaseWord);
    }

    bool ContainsWordsWithFamilyNameSuffix (const list <MultiPartDecoratedWord> & components) const
    {
		for (list <MultiPartDecoratedWord>::const_iterator word = components.begin (); word != components.end (); ++word)
        {
            if (HasFamilyNameSuffix (word->FirstPart ())) return true;
        }

        return false;
    }

    bool IsOnlyOneWordWithFamilyNameSuffix (const list <MultiPartDecoratedWord> & components) const
    {
        if (components.size () != 1) return false;
        tstring lowerCaseWord = (*components.begin ()).LastPart ();
        return HasFamilyNameSuffix (lowerCaseWord);
    }

	bool LooksLikeFio (const list <MultiPartDecoratedWord> & components) const
	{
        if (components.size () == 1)
        {
            // "она, Господь"
            if (IsSpecialWord (*components.begin ())) return false;

			if ((*components.begin ()).PartCount () == 1)
			{
				tstring lemma = (*components.begin ()).LastPart ();

                // Эти фамилии омонимичны местоименным прилагательным.
                // В случае, если они употреблены отдельно, считаем их фамилиями.
				if (lemma.compare (_T("иванов")) == 0) return true;
				if (lemma.compare (_T("петров")) == 0) return true;
			}
        }
            
        if (IsCommonNoun (components)) return false;

		// TODO
        //if (Топоним (components)) return false;

        if (ContainsWordsFromFioDictionary (components) 
            && (AllWordsLowercase (components) || CaseSuggestsName (components))) return true;

        if (EndsInOvoEvo (components)) return true;

        if (ContainsPatronymic (components)) return true;

        if (EndsWithOneOf ((*components.begin ()).LastPart (), _T("ы,ые,ие,ое,ее,ск,ки"))) return false;

        if (DoesNotLookLikeFio (components)) return false;

        if (components.size () == 1 && (*components.begin ()).PartCount () > 1 && 
            dictionary.ContainsFullLemma ((*components.begin ()).Lemma ())) return false;

        if ((components.size () == 3 || ContainsWordsWithFamilyNameSuffix (components)) && AllWordsTitleCase (components)) return true;

        // Одно слово типа "иванов" - это скорее всего familyName.
        if (IsOnlyOneWordWithFamilyNameSuffix (components)) return true;

        return components.size () == 1 
            && !NotAHomonym ((*components.begin ()).Lemma ()) 
            && (*(*components.begin ()).Parts ().begin ()).GetCaps ().IsFirstCap ();	
	}

	class Word : public IParsedPart
	{
	public:
		virtual RodChislo GetRodChislo () const = 0;

		virtual bool IsPossessiveAdjective () const = 0;

		virtual bool IsNonAdjectivalNoun () const = 0;

		virtual bool IsAnimate () const = 0;

		virtual bool UseNA () const = 0;

		virtual const tstring & Lemma () const = 0;
	};


	// Класс, реализующий преобразования основы 
	// при добавлении окончания (беглость гласной,
	// чередования согласных).
	// Аналог класса ОсноваИмени в .NET версии
	class WordMorpher { 
		tstring stem;
		tstring lemmaEnding;
		bool endingStressed;
		bool volatileVowel;

		enum StemType {Firm, Soft, Mixed};
		StemType stemType;

	public:
		WordMorpher (const tstring & lemma, size_t endingLength,
			bool endingStressed, bool volatileVowel)
			: endingStressed (endingStressed)
			, volatileVowel (volatileVowel)
		{
			// определяем тип основы
			bool stemTypeIsKnown = false;

			if (endingLength == 0 && (EndsWith (lemma, _T('ь')) || EndsWith (lemma, _T('й')))) {
				endingLength = 1;
				stemType = Soft;
				stemTypeIsKnown = true;
			}

			const size_t stemLength = lemma.length () - endingLength;
			this->stem = lemma.substr (0, stemLength);
			this->lemmaEnding = lemma.substr (stemLength);

			if (!stemTypeIsKnown) {
				switch (lemma [lemma.length () - 1 - endingLength]) {
					case _T('ж'): case _T('ш'):
					case _T('ч'): case _T('щ'):
					case _T('г'): case _T('к'): case _T('х'):
						stemType = Mixed;
						break;
					case _T('ц'): 
						stemType = Firm;
						break;
					default:
						stemType = StartsWithSoftening (lemmaEnding)
							? Soft : Firm;
						break;
				}
			}

		}

        // слова, имеющие особое окончание тв.п. мн.ч. -ЬМИ
        static bool IsAmiException (const tstring & stem) {
            static const TCHAR * x [] = {
                _T("люд"), _T("дет"), _T("лошад")
            };
            for (size_t i=0; i < sizeof (x) / sizeof (*x); ++i) {
                if (stem.compare (x [i]) == 0) return true;
                tstring polu = _T("полу");
                polu += x [i];
                if (stem.compare (polu) == 0) return true;
            }
            return false;
        }

		const tstring AddEnding (tstring ending) const 
		{
			// основа может измениться, так что
			// скопируем её в локальную переменную
			tstring stem = this->stem;

			if (ending.empty ()) {
				if (stemType == Soft) {
					ending = EndsWithConsonant (stem) ? _T("ь") : 
                        (EndsWith (stem, _T('й')) ? _T("") /*секвой*/: _T("й"));
				}
			} else {
				// обозначение мягкости основы и чередование 
				// о/е/ё в зависимости от ударения
				switch (ending [0]) {
					case _T('а'): 
						if (stemType == Soft) ending [0] = _T('я');
                        if (ending.compare (_T("ями")) == 0 && IsAmiException (stem)) {
                            ending = _T("ьми");
                        }
						break;
					case _T('у'): 
						if (stemType == Soft) ending [0] = _T('ю');
						break;
					case _T('ы'): 
						if (stemType != Firm) ending [0] = _T('и');
						break;
					case _T('о'): 
						TCHAR c;
						if (EndsWithSibilantOrTse (stem)) {
							c = endingStressed ? _T('о') : _T('е');
						} else if (stemType == Soft) {
							c = endingStressed ? _T('ё') : _T('е');
						} else {
							c = _T('о');
						}
						ending [0] = c;
						break;
					case _T('е'):
						if ((EndsWith (stem, _T('и')) 
							// в односложных основах (кий, Лия) оставляем -е:
							&& ContainsVowel (stem.substr (0, stem.length () - 1)) 
							&& !endingStressed)
                            ||
                            EndsWith (stem, _T("забыть"))) // забытье, полузабытье
						{
							ending = _T("и"); // дат. и предл.
						}
						break;
				}
			}
			if (volatileVowel && stem.length () >= 2) 
			{
				TCHAR c1 = stem [stem.length () - 1];
				TCHAR c2 = stem [stem.length () - 2];

				if (Slogovoe (lemmaEnding) && !Slogovoe (ending)) {
					// определить, какую гласную вставлять
					// и подготовить основу
					TCHAR vowel;
					if (c1 == _T('ь')) {
						// гостья, ущелье, статья, питьё и др.
						if (EndsWith (stem, _T("семь")) // семьи
                            || EndsWith (stem, _T("ружь")) // ружья
                            || EndsWith (stem, _T("судь"))) // судьи
                        {
							vowel = _T('е');
						} else {
							vowel = endingStressed ? _T('е') : _T('и');
						}
						// заменить Ь на Й (судья - судей)
						stem [stem.length () - 1] = _T('й');
						ending = _T("");
					} else if (c2 == _T('й') || c2 == _T('ь')) {
						if (EndsWith (stem, _T("яйц"))) { // яйцо
							vowel = _T('и');
						} else if (EndsWith (stem, _T("серьг"))) { // серьга
							vowel = _T('ё');
						} else if (EndsWith (stem, _T("тьм"))) { // тьма как _T('множество')
							vowel = _T('е'); // Р.мн. _T("тем")
						} else { // шпилька, письмо, чайка, кайма, кольцо и др.
							vowel = (c1 == _T('ц')) ? _T('е') : (endingStressed ? _T('ё') : _T('е'));
						}
						// убрать c2 (Ь или Й)
						stem.erase (stem.length () - 2, 1);
					} else if (IsGuttural (c2) || (IsGuttural (c1) && !IsSibilantOrTse (c2))) {
						// кукла, окно, сказка, ведёрко и др.
                        if (EndsWith (stem, _T("кочерг"))) { // кочерги
							vowel = _T('ё');
                        } else {
							vowel = _T('о');
                        }
					} else {
						if (EndsWith (stem, _T("шестерн")) // шестерня
                            || EndsWith (stem, _T("сёстр")) // сёстры
                            || EndsWith (stem, _T("сестр"))) // сёстры через Е
                        { 
							vowel = _T('ё');
                        } 
                        else if (EndsWith (stem, _T("деревн")) // деревни
                            || EndsWith (stem, _T("корчм"))) // корчмы
                        { 
                            // деревни - схема ударения е, окончание 
                            // ударно в косвенных множественного
							vowel = _T('е');
                        } 
                        else if (EndsWith (stem, _T("дн"))) { 
							vowel = _T('о'); // дно - донья
						} else {
							vowel = (c1 == _T('ц')) ? _T('е') : 
								(endingStressed ? (IsSibilantOrTse (c2) ? _T('о') : _T('ё')) : _T('е'));
						}
					}
					// вишня - вишен, читальня - читален и т.п.
					if (stemType == Soft && c1 == _T('н')) {
						if (!EndsWith (stem, _T("барышн"))  &&
							!EndsWith (stem, _T("боярышн")) &&
							!EndsWith (stem, _T("кухн"))    &&
							!EndsWith (stem, _T("деревн")))
						{
							ending = _T(""); // основа отвердевает
						}
					}
					if (vowel == _T('ё')) {
						// удалить другие Ё из основы этой формы
						for (size_t i=0; i<stem.length (); ++i) {
							if (stem [i] == _T('ё')) {
								stem [i] = _T('е');
							}
						}
					}
					// вставить гласную
					stem.insert (stem.length () - 1, 1, vowel);
				}
				else if (!Slogovoe (lemmaEnding) && Slogovoe (ending)) 
				{
					RemoveFleetingVowel (stem);
				}
			}

			//if (trial_expired ()) return stem + this->lemmaEnding;

			return stem += ending; // надо бы просто +, но в VC6&7 STL какой-то баг
		}

	private:

		static bool trial_expired ()
		{
			tm expiry;
			expiry.tm_year = 2013 - 1900;
			expiry.tm_mon = 1; // 0=January
			expiry.tm_mday = 1;
			expiry.tm_hour = 0;
			expiry.tm_min = 0;
			expiry.tm_sec = 0;
			expiry.tm_isdst = 1;

			time_t t = mktime (&expiry);

			time_t current_time = time (0);

			return (current_time > t);
		}

		static void RemoveFleetingVowel (tstring & stem)
		{
            if (stem.length () < 3) return;

    		TCHAR c3 = stem [stem.length () - 3];
    		TCHAR c2 = stem [stem.length () - 2];
    		TCHAR c1 = stem [stem.length () - 1];

       //     if (stem.length () > 3)
       //     {
    			//TCHAR c4 = stem [stem.length () - 4];

       //         // С одной стороны: игрец, беглец, борщец
       //         // С другой:
       //         // участок, костёр, свёрток, выкормок, столбец, 
       //         // волчец, волчок, фламандец, должок, желток, поползень, 
       //         // выползка, загвоздка, 
       //         if (!IsVowel (c3) && !IsVowel (c4) && !((c4=='з' && c3=='д') || (c4=='с' && c3=='т') || c4=='р' || c4=='л' || c4=='н')) return;
       //     }

			// убрать гласную
			if (c1 == _T('е') || c1 == _T('и')) 
			{
				// улья, воробья, муравья, третьего, птичьего
				// заменить гласную на разделительный Ь
				stem [stem.length () - 1] = _T('ь');
			} 
			else if (stem.length () > 2) 
			{
				// зверька, льда, пальца
				// орла, кашля, конца
				bool soften = (c3 == _T('л') && IsSoftening (c2))
					|| ((c2 == _T('е') || c2 == _T('ё')) && c1 == _T('к')
                         && !IsSibilantOrTse (c3)); // ребёночка, казачка (есть еще familyName Казачёк)

				if (soften) stem.insert (stem.length () - 2, 1, _T('ь'));

				// убрать гласную
				stem.erase (stem.length () - 2, 1);

				if (IsVowel (c3)) 
				{
					// боец - бойца
					stem.insert (stem.length () - 1, 1, _T('й'));
				}
			}
		}

		static bool Slogovoe (const tstring & ending) {
			return ending.length () != 0 // нулевое не является слоговым
				&& ending [0] != _T('й') 
				&& ending [0] != _T('ь'); // -ь, -ью
		}

		static bool IsSoftening (TCHAR c) {
            static const tstring s = _T("еияюё");
			return s.find (c) != tstring::npos;
		}

		static bool StartsWithSoftening (const tstring & s) {
			return !s.empty () && IsSoftening (s [0]);
		}

		static bool IsConsonant (TCHAR c) {
            static const tstring s = _T("бвгджзклмнпрстфхцчшщ");
			return s.find (c) != tstring::npos;
		}

		static bool IsVowel (TCHAR c) {
            static const tstring s = _T("аяоёиыуюэе");
			return s.find (c) != tstring::npos;
		}

		static bool EndsWithConsonant (const tstring & s) {
			return !s.empty () && IsConsonant (*s.rbegin ());
		}

		static bool IsSibilantOrTse (TCHAR c) {
            static const tstring s = _T("жшщчц");
			return s.find (c) != tstring::npos;
		}

		static bool EndsWithSibilantOrTse (const tstring & s) {
			return !s.empty () && IsSibilantOrTse (*s.rbegin ());
		}

		static bool ContainsVowel (const tstring & s) {
			return s.find_first_of (_T("аяоёиыуюэе")) != tstring::npos;
		}

		static bool IsGuttural (TCHAR c) {
			static const tstring s = _T("гкх");
			return s.find (c) != tstring::npos;
		}
	};

	class SingleWordWithGramInfo : public IPhrasePart
	{
		const SingularOrPluralGramInfo * gi;
		DecoratedWord word;

        static tstring GetForm2 (const SingularOrPluralGramInfo * gramInfo, const tstring & lemma, Padeg padeg, bool isAnimate)
        {
            if (padeg == Padeg_V && gramInfo->GetEndings ()->AccAnimEqualsGen ())
            {
                padeg = isAnimate
                    ? Padeg_R
                    : Padeg_I;
            }

            tstring ending = (*gramInfo->GetEndings ()) [padeg];

            // "в забытьи" - исключение
            if (padeg == Padeg_M && (EndsWith (lemma, _T("забытьё")) || EndsWith (lemma, _T("забытье"))))
            {
                ending = _T("и");
            }

            tstring form = WordMorpher (lemma, (*gramInfo->GetEndings ()) [Padeg_I].length (), gramInfo->EndingStressed (), gramInfo->FleetingVowel ()).AddEnding (ending);

            // "жилье, копье" - не надо ё
            if ((*gramInfo->GetEndings ()) [Padeg_I].compare (_T("о")) == 0 && gramInfo->EndingStressed () && EndsWith (lemma, _T('е')))
            {
				size_t i = form.find_last_of (_T('ё'));

				if (i != tstring::npos)
				{
					form [i] = _T('е');
				}
            }

            if (!Common::ContainsVowel (form)) return _T(""); // мгл

            return form;
        }

	public:

		SingleWordWithGramInfo (const SingularOrPluralGramInfo * gi, const DecoratedWord & word)
			: gi (gi), word (word)
		{
		}

		virtual tstring GetFormByCase (Padeg padeg, bool animate) const 
		{
            tstring form = GetForm2 (gi, word.Lemma (), padeg, animate);
			if (form.empty ()) return form;
            return word.ApplyTo (form);
		}

		virtual bool PaulcalIsSingular () const 
		{
			return gi->GetEndings ()->GetEndingsType () == EndingsType_NounLike;
		}
	};

	class DictionaryWord : public Word
	{
		const MultiPartDecoratedWord word;
		const GramInfo * const gi;

	public:
		DictionaryWord (const MultiPartDecoratedWord & word, const GramInfo * gi)
			: word (word), gi (gi)
		{
		}

		// Пока только для отладки.
		virtual const tstring & Lemma () const
		{
			return word.LastPart ();
		}

		virtual RodChislo GetRodChislo () const
		{
			return gi->rodChislo;
		}

		virtual bool IsPossessiveAdjective () const
		{
			return gi->GetEndings ()->GetEndingsType () == EndingsType_Pronomial;
		}

		virtual bool IsNonAdjectivalNoun () const
		{
			EndingsType endingsType = gi->GetEndings ()->GetEndingsType ();

			return gi->IsNoun 
				&& endingsType != EndingsType_Adjectival
				&& endingsType != EndingsType_Pronomial;
		}

		virtual bool IsAnimate () const
		{
			return gi->IsAnimate;
		}

		virtual bool UseNA () const
		{
			return gi->Na;
		}

		virtual bool AppendTo (PhraseBuilder & phraseBuilder, list <tstring>::const_iterator & separator, bool plural) const
		{
            for (size_t i=0; i < word.PartCount () - 1; ++i)
            {
                phraseBuilder.Add (word.Parts () [i].DecoratedLemma ());
                phraseBuilder.Add (*separator++);
            }

			DecoratedWord lemma = *word.Parts ().rbegin ();

            if (plural)
            {
				const Endings * pluralEndings = gi->GetEndingsPlural ();
				if (pluralEndings == NULL) return false;
            }

            phraseBuilder.Add (new SingleWordWithGramInfo (
				plural ? gi->GetPlural () : gi->GetSingular (), 
				plural ? lemma.Clone (GetPluralLemma ()) : lemma));

			return true;
		}

		tstring GetPluralLemma () const
		{
            const Endings * pluralEndings = gi->GetEndingsPlural ();

            tstring pluralLemma = 
				gi->Alteration ? ApplyAlterations (word.LastPart ()) : 
				WordMorpher (
                    word.LastPart (), 
                    (*gi->GetEndings ()) [Padeg_I].length (), 
                    gi->EndingIsStressed (), 
                    gi->GetSingular ()->FleetingVowel ()).AddEnding ((*pluralEndings) [Padeg_I]);

            // звезда - звёзды, село - сёла
            if (gi->EndingIsStressed () && !gi->EndingIsStressedPlural ())
            {
                size_t i = pluralLemma.find_last_of (_T('е'));

                if (i != tstring::npos)
                {
                    pluralLemma [i] = _T('ё');
                }
            }

			return pluralLemma;
		}

        static tstring ApplyAlterations (tstring singularLemma)
        {
			if (singularLemma == _T("бесёнок") || singularLemma == _T("бесенок")) return _T("бесенята");
			if (singularLemma == _T("чертёнок") || singularLemma == _T("чертенок")) return _T("чертенята");

			static const TCHAR * s [] = {
				_T("онок"),_T("ата"),
				_T("енок"),_T("ята"),
				_T("ёнок"),_T("ята"),
				_T("оночек"),_T("атки"),
				_T("еночек"),_T("ятки"),
				_T("ёночек"),_T("ятки"),
				_T("ин"),_T("е"),
				_T("г"),_T("зья"),
				_T("к"),_T("чья"),
				_T("ь"),_T("ья"),
				_T(""),_T("ья")};

			for (unsigned i = 0; i < sizeof (s) / sizeof (*s); i += 2)
            {
                tstring singularSide = s [i];
                tstring pluralSide = s [i+1];

                // перо, дерево...
                if (EndsWith (singularLemma, _T("о"))) singularSide += _T("о");

                if (singularLemma.length () - 1 < singularSide.length ()) continue;

                // клин
                if (EndsWith (singularLemma, singularSide))
                {
                    tstring lemma = singularLemma.substr (0, singularLemma.length () - singularSide.length ());
                    if (singularSide.length () > 1 && ! Common::ContainsVowel (lemma)) continue;
                    return lemma + pluralSide;
                }
            }

            return singularLemma;
        }
	};

    static bool ContainsVowel (const tstring & s) 
	{
		const tstring vowels = _T("аяоёиыуюэе");
        return s.find_first_of (vowels) != tstring::npos;
    }

    /// <summary>
    /// Определить тип склонения простого слова (слова без дефисов)
    /// в соответствии с основным словарём и словарём особых слов.
    /// </summary>
    void FindDeclensionForSimpleWord (const MultiPartDecoratedWord & word, bool first, list <Ptr <Word> > & result) const
    {
		// TODO
        //// Обработка сложносокращённых типа "завскладом, завотделением, завкадрами".
        //if (word.PartCount == 1 && Common.СловоТипаЗавскладом (word.FirstPart))
        //{
        //    ДобавитьНесклоняемое (word, result, RodChislo.SingMasc);
        //    ДобавитьНесклоняемое (word, result, RodChislo.SingFem);

        //    return result;
        //}

        // поискать это слово в словаре особых слов
        specialDictionary.Find (word, result);
        if (!result.empty ()) return;

		// если не нашли, то поискать в основном словаре
		list <const GramInfo *> gis;
		dictionary.Find (word.LastPart (), first, gis); // TODO: first, Lemma not LastPart

		for (list <const GramInfo *>::const_iterator gi = gis.begin (); gi != gis.end (); ++gi)
		{
			result.push_back (Ptr <Word> (new DictionaryWord (word, *gi)));
		}
    }

	class SpecialDictionary // C#: СловарьОсобыхСлов
	{
		class SpecialWord : public Object // C#: ОсобоеСлово
		{
			const vector <tstring> singularForms;

			Ptr <const SpecialWord> plural; // Ptr используется для упрощения управления памятью - не нужен деструктор, operator = и конструктор копии

            const RodChislo rodChislo;
            const bool IsNoun;

            /// <summary>
            /// false означает, что либо это неодушевлённое существительное,
            /// либо категория одушевлённости неприменима к этому слову
            /// (если это прилагательное или местоимение).
            /// </summary>
            const bool isAnimate;

		public:

            SpecialWord (
				RodChislo rodChislo, 
				bool isAnimate, 
				bool IsNoun, 
				const vector <tstring> & singularForms, 
				const vector <tstring> & pluralForms)
				: singularForms (singularForms)
				, plural (pluralForms.empty () ? NULL : new SpecialWord (Plural, isAnimate, 
					IsNoun, pluralForms, vector <tstring> ()))
                , rodChislo (rodChislo)
                , IsNoun (IsNoun)
                , isAnimate (isAnimate)
            {
            }

			const tstring & Lemma () const
			{
				return singularForms [0];
			}

			bool IsNonAdjectivalNoun () const
			{
				return IsNoun;
			}

			RodChislo GetRodChislo () const
			{
				return rodChislo;
			}

			bool IsAnimate () const
			{
				return isAnimate;
			}

			const SpecialWord * GetPlural () const
			{
				return plural.GetP ();
			}

            tstring GetFormByCase (Padeg padeg, bool anim) const
            {
                tstring form = GetFormByCaseInternal (padeg, anim);
				return form == _T("-") ? tstring (_T("")) : form;
            }

		private:

            tstring GetFormByCaseInternal (Padeg padeg, bool anim) const
            {
                switch (padeg)
                {
                    case Padeg_I: return singularForms [0];
                    case Padeg_R: return singularForms [1];
                    case Padeg_D: return singularForms [2];
                    case Padeg_V: return singularForms [3].empty () 
                        ? anim 
                            ? singularForms [1] // родительный
                            : singularForms [0] // именительный
                        : singularForms [3];
                    case Padeg_T: return singularForms [4];
                    case Padeg_P: return singularForms [5];
                    case Padeg_M: return singularForms [5];
					default: throw runtime_error ("Неправильный padeg");
                }
            }
		};

		class DecoratedSpecialWord : public Word, public IPhrasePart
		{
			const MultiPartDecoratedWord word;
			const SpecialWord & specialWord;

		public:
			DecoratedSpecialWord (MultiPartDecoratedWord word, const SpecialWord & specialWord)
				: word (word)
				, specialWord (specialWord)
			{
			}

			bool IsNonAdjectivalNoun () const
			{
				return specialWord.IsNonAdjectivalNoun ();
			}

			RodChislo GetRodChislo () const
			{
				return specialWord.GetRodChislo ();
			}

			bool IsAnimate () const
			{
				return specialWord.IsAnimate ();
			}

			bool IsPossessiveAdjective () const
			{
				return false;
			}

			virtual bool AppendTo (PhraseBuilder & phraseBuilder, list <tstring>::const_iterator & separator, bool plural) const
			{
				const DecoratedSpecialWord * word = plural 
					? GetPlural () 
					: this;

				if (!word) return false;

				phraseBuilder.Add (word);

				return true;
			}

			DecoratedSpecialWord * GetPlural () const
			{
				const SpecialWord * plural = specialWord.GetPlural ();
                    
				return plural == 0 ? 0 : new DecoratedSpecialWord (word, *plural);
			}

			// IPhrasePart.GetFormByCase 
		    virtual tstring GetFormByCase (Padeg padeg, bool isAnimate) const
			{
				tstring form = specialWord.GetFormByCase (padeg, isAnimate);
				if (form.empty ()) return _T("");
				return word.Parts () [0].ApplyTo (form);
			}

			// IPhrasePart.PaulcalIsSingular
			bool PaulcalIsSingular () const
			{
				return IsNonAdjectivalNoun ();
			}

			bool UseNA () const
			{
				// Пока среди особых нет с предлогом НА.
				return false;
			}

			const tstring & Lemma () const 
			{
				return specialWord.Lemma ();
			}
		};

		class Less
		{
		public:
			bool operator () (const SpecialWord & w1, const SpecialWord & w2) const
			{
				return w1.Lemma () < w2.Lemma ();
			}
		};

		typedef std::multiset <SpecialWord, Less> Words;

		Words words;

	public:

		SpecialDictionary ()
		{
			{
				stringstream file (string ((char * ) specialDict, specialDict_size));

				LemmaReader lemmaReader (file, LemmaReader::CompareFromBeginning);

				while (true)
				{
					unsigned char b = file.get ();

					if (file.eof ()) break;

					RodChislo rodChislo = (RodChislo) (b & 3);

					bool plural = (b & 4) != 0;

					vector <tstring> singularForms = ReadForms (lemmaReader);

					vector <tstring> pluralForms = plural ? ReadForms (lemmaReader) : vector <tstring> ();

					bool anim = (b & 8) != 0;

					Add (true, rodChislo, anim,  singularForms, pluralForms);

					if (plural)
					{
						Add (true, Plural, anim, pluralForms, vector <tstring> ());
					}
				}
			}

            // Инициализировать особые прилагательные.
            {
				stringstream file (string ((char * ) specialAdjDict, specialAdjDict_size));

				LemmaReader lemmaReader (file, LemmaReader::CompareFromBeginning);

				while (true)
				{
					char c = file.get ();

					if (file.eof ()) break;

					file.putback (c);

                    vector <tstring> singularMasculineForms  = ReadForms (lemmaReader);
                    vector <tstring> singularFeminineForms  = ReadForms (lemmaReader);
                    vector <tstring> singularNeuterForms = ReadForms (lemmaReader);
                    vector <tstring> pluralForms     = ReadForms (lemmaReader);

                    Add (false, Plural,   false, pluralForms, vector <tstring> ()); // такой порядок нужен для "всё/все"
                    Add (false, SingMasc, false, singularMasculineForms, pluralForms);
                    Add (false, SingFem,  false, singularFeminineForms, pluralForms);
                    Add (false, SingNeut, false, singularNeuterForms, pluralForms);
                }
            }
		}

	private:

        vector <tstring> ReadForms (LemmaReader & lemmaReader)
        {
            unsigned char b = lemmaReader.Stream ().get ();

            vector <tstring> singularForms (6);

            for (size_t i=0; i < singularForms.size (); ++i)
            {
                unsigned char unused;

                tstring lemma = lemmaReader.GetNext (&unused);

				singularForms [i] = ((b & (1 << i)) == 0) ? tstring (_T("-")) : lemma;
            }

            return singularForms;
        }

        void Add (bool IsNoun, RodChislo rodChislo, bool anim, vector <tstring> singularForms, const vector <tstring> & pluralForms)
        {
			const tstring & lemma = singularForms [0];
            bool YoMakesADifference = !lemma.empty () && lemma [0] == _T('Ё') && lemma [1] == _T('!') ;
            if (YoMakesADifference) singularForms [0] = singularForms [0].substr (2);

            SpecialWord word (rodChislo, anim, IsNoun, singularForms, pluralForms);

            words.insert (word);

            if (word.Lemma ().find (_T('ё')) != tstring::npos && !YoMakesADifference)
            {
                Add (IsNoun, rodChislo, anim, RemoveYo (singularForms), RemoveYo (pluralForms));
            }
        }

        static const vector <tstring> RemoveYo (const vector <tstring> & forms)
        {
            vector <tstring> r (forms.size ());

            for (size_t i=0; i < r.size (); ++i)
            {
				tstring s = ReplaceYo (forms [i]);
				r [i] = s;
            }

            return r;
        }

		static tstring ReplaceYo (tstring s)
		{
			for (size_t i = 0; i < s.length (); ++i)
			{
				if (s [i] == _T('ё')) s [i] = _T('е');
			}

			return s;
		}

	public:

	    void Find (const MultiPartDecoratedWord & word, list <Ptr <Word> > & result) const
		{
			tstring lemma = word.Lemma ();
			vector <tstring> forms (1);
			forms [0] = lemma;
			SpecialWord val (Plural, false, false, forms, forms);

			Words::const_iterator it = words.find (val);

			while (it != words.end () && it->Lemma () == lemma)
			{
				result.push_back (Ptr <Word> (new DecoratedSpecialWord (word, *it++)));
			}
		}
	};

	SpecialDictionary specialDictionary;

    void FindDeclension (const MultiPartDecoratedWord & word, bool first, list <Ptr <Word> > & result) const
	{
        // Часто этому условию удовлетворяют предлоги (в, с, к).
        if (! ContainsVowel (word.LastPart ())) return;

		// TODO
        //if (ОбщийСловарь.ContainsFullLemma (word.Lemma))
        //{
        //    return FindDeclensionForSimpleWord (word, first);
        //}

        //if (СклоняетсяТолькоПерваяЧасть (word))
        //{
        //    LinkedList <Word> result = new LinkedList <Word> ();
        //    foreach (Word word in FindDeclensionForSimpleWord (new MultiPartDecoratedWord (word.Parts [0]), first))
        //    {
        //        result.AddLast (new СловоТипаРостовНаДону (word, word));
        //    }
        //    return result;
        //}

        //if (word.FirstPart.StartsWith ("пол") && (Common.EndsWithOneOf (word.LastPart, "ого,его")/* && word.Length > 8*/))
        //{
        //    // полпервого, полвторого, полтретьего, ... пол-одиннадцатого, полдвенадцатого
        //    LinkedList <Word> result = new LinkedList <Word> ();
        //    ДобавитьНесклоняемое (word, result, RodChislo.SingNeut);
        //    return result;
        //}

		//AnalyzeHyphenatedWord (word, first, result);
  //      if (!result.empty ()) return;

        FindDeclensionForSimpleWord (word, first, result);
	}

	//template <typename T, typename Collection, typename Transform>
	//static vector <T> VectorOf <T> (const Collection & coll, const Transform & transform)
	//{
	//	vector <T> result;

	//	result.reserve (coll.size ());

	//	for (auto it = coll.begin (); it != coll.end (); ++it)
	//	{
	//		result.push_back (Transform (*it));
	//	}

	//	return result;
	//}

	//typedef bool (*IsOnlyLastPartInflectedDelegate) (const vector <tstring> & parts, IsOnlyLastPartInflectedDelegate f);

    /// <summary>
    /// Этот метод определяет, есть ли в составном слове части,
    /// стоящие перед дефисом, которые склоняются.
    /// Возвращает пустой список, если решит, что у этого слова склоняется только
    /// последняя часть (или не склоняется - по основному словарю).
    /// </summary>
  //  void AnalyzeHyphenatedWord (const MultiPartDecoratedWord & word, bool first, list <Ptr <Word> > & result)
  //  {
  //      // Слова с дефисами условно поделены на две категории:
  //      // 1) слова, у которых склоняется только последняя часть и
  //      // 2) слова из двух частей, обе из которых склоняются.
  //      // Вне этих категорий находятся слова типа "Ростов-на-Дону"
  //      // и слова типа "что-нибудь", которые здесь не учитываются.

  //      if (word.PartCount () < 2) return; // в слове нет дефиса

		//auto parts = VectorOf <tstring> (word.Parts (), [] (const DecoratedWord & w) {return w.Lemma();});

  //      if (Common.IsOnlyLastPartInflected (
  //          parts,  
  //          IsOnlyLastPartInflected)) return;

  //      // будем пытаться склонять все части 

  //      // TODO: "кран-балка, блок-схема" - склонять по правилу, а не по словарю
  //      // TODO: "йо-йо, чау-чау" и т.п., которых нет в словаре

  //      var варианты = new LinkedList <Слово> [parts.size()];
  //      var attributes = new List <РодЧисло> [parts.size()];

  //      bool неоднозначность = false;

  //      for (int i=0; i < parts.Length; ++i) {
  //          варианты [i] = НайтиВариантыСклоненияПростогоСлова (new MultiPartWord  (parts [i]), first);
  //          if (варианты [i].Count == 0) return result; // "завей-борода"
  //          attributes [i] = GetUniqueAttributes (варианты [i]);
  //          if (варианты [i].Count > 1) неоднозначность = true;
  //      }

  //      Слово [] части;

  //      if (неоднозначность)
  //      {
  //          // Пытаемся устранить неоднозначность, выбирая варианты, согласующиеся по роду и числу
  //          List <РодЧисло> intersection = attributes [0];
  //          for (int i=1; i < parts.Length; ++i) {
  //              intersection = Intersect (intersection, attributes [i]);
  //              if (intersection.Count == 0) break;
  //          }

  //          if (intersection.Count > 0) {
  //              // части согласуются по роду и числу
  //              части = new Слово [parts.Length];
  //              РодЧисло родЧисло = intersection [0];
  //              for (int i=0; i < parts.Length; ++i) {
  //                  части [i] = ВыбратьСлово (родЧисло, варианты [i]);
  //              }
  //          } else {
  //              // части не согласуются по роду и числу;
  //              // проверим, может быть, они согласуются хотя бы по числу,
  //              // например: "диван-кровать, кресло-качалка, школа-интернат, кабина-шлюз"
  //              bool всеЕдин = true;
  //              bool всеМнож = true;
  //              Слово [] частиЕдин = new Слово [parts.Length];
  //              Слово [] частиМнож = new Слово [parts.Length];
  //              for (int i=0; i < parts.Length; ++i) {
  //                  частиЕдин [i] = НайтиСловоЗаданногоЧисла (варианты [i], false);
  //                  частиМнож [i] = НайтиСловоЗаданногоЧисла (варианты [i], true);
  //                  if (частиЕдин [i] == null) всеЕдин = false;
  //                  if (частиМнож [i] == null) всеМнож = false;
  //              }

  //              if (всеЕдин) части = частиЕдин;
  //              else if (всеМнож) части = частиМнож;
  //              else return result; // не согласуются ни по роду, ни по числу
  //          }
  //      }
  //      else
  //      {
  //          // склоняем обе части независимо друг от друга (ясли-сад)
  //          части = new Слово [parts.Length];
  //          for (int i=0; i < parts.Length; ++i) 
  //          {
  //              части [i] = варианты [i].First.Value;
  //          }
  //      }
  //         
  //      result.AddLast (new СловоСДефисами (word, части));

  //      return result;
  //  }

	static unsigned GetUniqueAttributes (const list <Ptr <Word> > & words)
	{
		unsigned attr = 0;

		for (list <Ptr <Word> >::const_iterator word = words.begin (); word != words.end (); ++word)
		{
			attr |= (1 << (unsigned) (*word)->GetRodChislo ());
		}

		return attr;
	}

	static bool IsStopWord (const MultiPartDecoratedWord & word)
	{
		// TODO 
		return false;
	}

	bool CanBeGenitive (const MultiPartDecoratedWord & word) const
	{
		return dictionary.CanBeGenitive (word.LastPart ());
	}

    void AnalyseNaric (
        list <MultiPartDecoratedWord> & components, 
        list <tstring> & separators,
        Attributes flags,
        RodChislo * rodChislo,
        bool * isAnimate,
        bool * prepositionNA,
		list <Ptr <IParsedPart> > & result) const
	{
        list <MultiPartDecoratedWord>::const_iterator word = components.begin ();

        if (! Common::ContainsVowel (word->LastPart ()))
        {
            // вероятно, аббревиатура
            return;
        }

        list <list <Ptr <Word> > > matches;

		matches.push_back (list <Ptr <Word> > ());

		FindDeclension (*word, true, *matches.begin ());

        if ((*matches.begin ()).empty ())
        {
            return;
        }

		// Битовая маска, хранящая набор пар признаков (род, число), 
        // по которым согласуются все просмотренные до сих пор слова.  
        // Продолжаем просмотр, пока не кончатся слова или не встретится слово, 
        // которое не согласуется со словами до него.
        unsigned attributes = GetUniqueAttributes (*matches.begin ());

		for (++word; word != components.end (); ++word) 
        {
            if (IsStopWord (*word)) break;

            if (attributes != (1 << Plural) && CanBeGenitive (*word))
            {
				list <MultiPartDecoratedWord>::const_iterator nextWord = word;
				
				++nextWord;

                if (nextWord != components.end () && CanBeGenitive (*nextWord))
                {
                    // следующее слово тоже в родительном
                    break;
                }
            }

			list <Ptr <Word> > currentMatches;

			FindDeclension (*word, false, currentMatches);

			unsigned atts = attributes;

            atts &= GetUniqueAttributes (currentMatches);

            if (atts == 0) break;

			attributes = atts;

            // значит, текущее слово согласуется с предыщущими

            // сохранить варианты анализа этого слова
            matches.push_back (currentMatches);
        }

        // выбираем из пар {род, число} ту, что принадлежит первому слову (так что моль склоняется правильно)
        *rodChislo = GetRodChislo (attributes, *matches.begin ());

        return SecondPass (matches, components, separators, flags, *rodChislo, isAnimate, prepositionNA, result);
	}

	static RodChislo GetRodChislo (unsigned attributes, const list <Ptr <Word> > & matches)
	{
		assert (attributes != 0);

		for (list <Ptr <Word> >::const_iterator i = matches.begin (); i != matches.end (); ++i)
		{
			RodChislo rodChislo = (*i)->GetRodChislo ();

			if (((1 << ((unsigned) rodChislo)) & attributes) != 0)
			{
				return rodChislo;
			}
		}

		throw runtime_error ("GetRodChislo");
	}

    static void SecondPass (
        const list <list <Ptr <Word> > > & matches, 
        const list <MultiPartDecoratedWord> & components, 
        const list <tstring> & separators,
        Attributes flags,
        RodChislo rodChislo,
        bool * isAnimate,
        bool * prepositionNA,
		list <Ptr <IParsedPart> > & words)
    {
        // Из оставшихся вариантов разбора выбираем первые 
        // согласующиеся с этими родом и числом;
        // в склоняемые включается только одно существительное,
        // остальные идут в uninflected, например: звон колоколов,
        // паттерны проектирования (хотя эти существительные не в именительном,
        // они зачастую всё равно распознаются как существительные).
        Word * rootWord = 0;
        bool animateMet = false;

        list <tstring>::const_iterator delimNode = separators.begin ();

        list <MultiPartDecoratedWord>::const_iterator word = components.begin ();

        for (list <list <Ptr <Word> > >::const_iterator currentWordMatches = matches.begin (); 
			currentWordMatches != matches.end (); ++currentWordMatches)
        {
			// Пропустить столько разделителей, сколько частей в слове.
			for (size_t i=0; i < word->PartCount (); ++i) ++delimNode;

            ++word;

            // из вариантов разбора текущего слова
            // находим первый, согласующийся с найденной 
            // в первом проходе парой (род, число)
            Word * wordThatMatches = GetWordThatMatches (*currentWordMatches, rodChislo);

            // "Комната Ленина"
            if (rootWord != 0 && wordThatMatches->IsPossessiveAdjective ()) break;

            if (wordThatMatches->IsNonAdjectivalNoun ())
            {
                if (rootWord != 0) break;
                rootWord = wordThatMatches;
            }

            if (wordThatMatches->IsAnimate ()) animateMet = true;

            if (delimNode->find_first_of (_T(",-(")) != tstring::npos
                && word != components.end () && !Participle (word->LastPart ())) // "данайцы, приносящие дары"
            {
                rootWord = 0;
            }

            words.push_back (Ptr <IParsedPart> (wordThatMatches));
        }

        *isAnimate = rootWord != 0 ? rootWord->IsAnimate () : animateMet;

        // Топонимы все неодушевленные, даже Вождь Пролетариата.
        *isAnimate &= (flags & Attribute_Toponym) == 0;

        *prepositionNA = rootWord == 0 ? false : rootWord->UseNA ();
    }

	static Word * GetWordThatMatches (const list <Ptr <Word> > & words, RodChislo rodChislo)
	{
		for (list <Ptr <Word> >::const_iterator word = words.begin (); word != words.end (); ++word)
		{
			if ((*word)->GetRodChislo () == rodChislo) return &**word;
		}

        throw runtime_error ("GetWordThatMatches: error.");
	}

    typedef void (RussianDeclensor::*AnalysisMethod) (
        list <MultiPartDecoratedWord> & components, 
        list <tstring> & separators,
        Attributes attributes,
        RodChislo * rodChislo,
        bool * isAnimate,
        bool * prepositionNA,
		list <Ptr <IParsedPart> > & result) const;

    AnalysisMethod ChooseParser (
        const list <MultiPartDecoratedWord> & components, 
		Attributes attributes) const
    {
        if ((attributes & Attribute_Fio) != 0 && (attributes & Attribute_Naric) != 0)
        {
            throw runtime_error ("Указаны взаимоисключающие признаки: ФИО и IsCommonNoun.");
        }

        if ((attributes & (Attribute_Fio | Attribute_Masc | Attribute_Fem)) != 0)
        {
			// Указание на мужской или женский род означает, что это имя.
            return &RussianDeclensor::AnalyseFio;
        }

        if ((attributes & Attribute_Naric) != 0)
        {
            return &RussianDeclensor::AnalyseNaric;
        }

		return (LooksLikeFio (components)) ? &RussianDeclensor::AnalyseFio : &RussianDeclensor::AnalyseNaric;
    }

    static bool Flatten (
        PhraseBuilder & phraseBuilder, 
        const list <Ptr <IParsedPart> > & parsedComponents, 
        const list <MultiPartDecoratedWord> & components, 
        const list <tstring> & separators,
		bool plural)
    {
		list <tstring>::const_iterator separator = separators.begin ();

        phraseBuilder.Add (*separator++);

        list <MultiPartDecoratedWord>::const_iterator component = components.begin ();

		for (list <Ptr <IParsedPart> >::const_iterator parsedComponent = parsedComponents.begin ();
			parsedComponent != parsedComponents.end ();
			++parsedComponent)
        {
            if (!(*parsedComponent)->AppendTo (phraseBuilder, separator, plural))
			{
				return false;
			}

	        phraseBuilder.Add (*separator++);

            ++component;
        }

        // Если проанализировано меньше составляющих, чем было на входе,
        // записать оставшиеся составляющие в исходном виде, не склоняя.
        for (; component != components.end (); ++component)
        {
            for (size_t part = 0; part < component->PartCount (); ++part)
            {
                if (part > 0) phraseBuilder.Add (*separator++);
                phraseBuilder.Add (component->Parts () [part].DecoratedLemma ());
            }

			phraseBuilder.Add (*separator++);
        }

		return true;
    }

public:

	RussianDeclensor () {}
	 
	virtual ~RussianDeclensor () {}

	// Вызывающий должен сделать delete возвращенному значению,
	// а также свойству Phrase.Plural, если оно не NULL.
	virtual Phrase * Analyse (const tstring & s, Attributes attributes) const
	{
		Phrase * phrasePtr = AnalyseInternal (s, attributes);

		Exception searchException = {{s.c_str()}};

		Exception * exceptionDictEnd = exceptionDict + exceptionDict_size;

		Exception * it = std::lower_bound (exceptionDict, exceptionDictEnd, searchException, 
			[] (const Exception & e1, const Exception & e2) {return tstrcmp (e1.singular.nominative, e2.singular.nominative) < 0; });

		if (it != exceptionDictEnd)
		{
		}

		return phrasePtr;
	}

	Phrase * AnalyseInternal (const tstring & s, Attributes attributes) const
	{
		if (s == _T("Проектная документация без сметы и результаты инженерных изысканий"))
		{
			Phrase * phrase1 = Analyse (_T("Проектная документация без сметы и "), attributes);
			Phrase * phrase2 = Analyse (_T("результаты инженерных изысканий"), attributes);

			return new Phrase (phrase1, phrase2);
		}

		list <DecoratedWord> decoratedWords;
		list <tstring> separators;

		Tokenize (s, decoratedWords, separators);

        RemoveNonRussian (decoratedWords, separators);

        if (decoratedWords.empty ()) return 0; // нет русских слов

        list <MultiPartDecoratedWord> components;
		
		JoinHypenatedWords (decoratedWords, separators, components);

        // Разбить входную строку на части, разделенные тире.
        list <TokenizedString> dashDelimitedStrings;

        list <MultiPartDecoratedWord> currentWordList;
        list <tstring> currentDelimList;

        Move (currentWordList, components);
        Move (currentDelimList, separators, currentWordList);

        while (! components.empty ())
        {
            if (IsDash (*separators.begin ()))
            {
                Move (currentDelimList, separators);
                separators.push_front (_T(""));
                dashDelimitedStrings.push_back (TokenizedString (currentWordList, currentDelimList));
                currentWordList.clear ();
                currentDelimList.clear ();
            }

            Move (currentWordList, components);
            Move (currentDelimList, separators, currentWordList);
        }

        Move (currentDelimList, separators);
        dashDelimitedStrings.push_back (TokenizedString (currentWordList, currentDelimList));

		assert (separators.empty ());

        RodChislo rodChislo = SingMasc;
        bool animate = false;
        bool prepositionNA = false;

        bool first = true;

        PhraseBuilder phraseBuilder;
        PhraseBuilder phraseBuilderPlural;

		bool plural = true;

        for (list <TokenizedString>::iterator tokenizedString = dashDelimitedStrings.begin (); 
			tokenizedString != dashDelimitedStrings.end (); ++tokenizedString)
        {
            bool anim;
            RodChislo rch;

            list <Ptr <IParsedPart> > comp;

			AnalysisMethod analyse = ChooseParser (tokenizedString->Words (), attributes);

			const RussianDeclensor & morpher = *this; // couldn't figure out the synax for calling through 'this'

			(morpher.*analyse) (
                tokenizedString->Words (), tokenizedString->Delims (), attributes, 
				&rch, &anim, &prepositionNA, comp);

            if (comp.empty ()) return 0; // какой-нибудь мусор вроде "р".

            if (anim) animate = true;
            if (first) rodChislo = rch;

            Flatten (phraseBuilder, comp, tokenizedString->Words (), tokenizedString->Delims (), false);

            if (plural)
            {
                if (!Flatten (phraseBuilderPlural, comp, tokenizedString->Words (), tokenizedString->Delims (), true))
				{
					plural = false;
				}
            }

            first = false;
        }

        return new Phrase (phraseBuilder.Parts (), rodChislo, animate, prepositionNA, 
            !plural ? 0 : new Phrase (phraseBuilderPlural.Parts (), Plural, animate, prepositionNA, 0));
	}
};

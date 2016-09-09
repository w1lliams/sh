 
#pragma once

#include "../Tokenizer.h"

#include <string>
#include <vector>
#include <map>
#include <sstream>

using namespace std;

// defined in UkrDict.cpp
extern int ukr_dict_size;
extern char ukr_dict [];

namespace UkrMorpher
{
	static bool EndsWith (const tstring & s, const tstring & ending) 
	{
		if (s.length () < ending.length ()) return false;

		tstring::const_reverse_iterator si = s.rbegin ();
		tstring::const_reverse_iterator ei = ending.rbegin ();

		while (ei != ending.rend ())
		{
			if (*si++ != *ei++) return false;
		}

		return true;
	}

	static bool HasEnding (const tstring & s, const tstring & ending)
    {
        return s.length () > ending.length () && EndsWith (s, ending);
    }

    static bool EndsWithOneOf (const tstring & s, const tstring & endings)
    {
		Tokenizer t (endings, Tokenizer::Ukrainian);

		for (;;)
		{
			t.GetNextSeparator ();
			tstring ending = t.GetNextToken ();
			if (ending.empty ()) break;
			if (HasEnding (s, ending)) return true;
		}

		return false;
	}


	enum RodChislo
	{
		SingMasc, SingFem
	};

	class StemAlterations
	{
		unsigned char v;

	public:
		StemAlterations () : v (0) {}

		StemAlterations (
			bool io,
			bool ie,
			bool stemSoftens,
			bool isNoun,
			bool fleetingVowel) 
			: v ((unsigned char) io 
				+ ((unsigned char) ie << 1) 
				+ ((unsigned char) stemSoftens << 2)
				+ ((unsigned char) isNoun << 3)
				+ ((unsigned char) fleetingVowel << 4))
		{}

		bool IO ()            const {return (v & 1)  != 0;}
		bool IE ()            const {return (v & 2)  != 0;}
		bool StemSoftens ()   const {return (v & 4)  != 0;} // (Iгор - Iгоря)
		bool IsNoun ()        const {return (v & 8)  != 0;}
		bool FleetingVowel () const {return (v & 16) != 0;} // Заєць - Зайця, Журавель - Журавля
	};

	class GramInfo
	{
		RodChislo rodChislo;
		StemAlterations stemAlterations;

	public:
		GramInfo (RodChislo rodChislo, StemAlterations stemAlterations) 
			: rodChislo (rodChislo)
			, stemAlterations (stemAlterations)
		{}

		RodChislo GetRodChislo () const {return rodChislo;}

		// Пока часть речи совпадает с типом склонения.
		bool IsNoun () const {return stemAlterations.IsNoun ();}

		StemAlterations GetStemAlterations () const {return stemAlterations;}
	};

	static bool IsVowel (TCHAR c)
	{
		return tstring (_T("аяоуюеєиії")).find (c) != tstring::npos;
	}

	static bool IsVowelOrApostrophe (TCHAR c)
	{
		return IsVowel (c) || Caps::IsApostrophe (c);
	}

	class Dictionary
	{
		typedef std::multimap <tstring, GramInfo> Entries;
		Entries entries;

		enum PartOfSpeech
		{
			MasculineAnimate, FeminineAnimate, MasculineInanimate, FeminineInanimate, Adjective
		};

	public:

		Dictionary ()
		{
			stringstream file (string (ukr_dict, ukr_dict_size));

			LemmaReader lemmaReader (file, LemmaReader::CompareFromBeginning);

			for (;;)
			{
				unsigned char b;

				tstring lemma = lemmaReader.GetNext (&b);

				if (lemma.empty ()) break;

				PartOfSpeech partOfSpeech = (PartOfSpeech) ((b >> 1) & 7);

				bool fleetingVowel = (b & 16) != 0;

				StemAlterations stemAlterations (
					Replace (lemma, _T('і'), _T('О')),
					Replace (lemma, _T('і'), _T('Е')),
					Replace (lemma, _T('р'), _T('Р')),
					partOfSpeech != Adjective,
					fleetingVowel);

				switch (partOfSpeech)
				{
					case MasculineAnimate:
					case MasculineInanimate:
						Add (lemma, GramInfo (SingMasc, stemAlterations));
						break;
					case FeminineAnimate:
					case FeminineInanimate:
						Add (lemma, GramInfo (SingFem, stemAlterations));
						break;
					case Adjective:
						{
							// заменить -ИЙ на -А:
							tstring lemmaFeminineInanimate (lemma.begin (), -- -- lemma.end ());
							lemmaFeminineInanimate += *++lemma.rbegin () == _T('и') 
								? _T("а")  // біла
								: _T("я"); // синя

							Add (lemma,  GramInfo (SingMasc, stemAlterations));
							Add (lemmaFeminineInanimate, GramInfo (SingFem, stemAlterations));
						}
						break;
					default:
						throw runtime_error ("Invalid POS.");
				}
			}
		}

		static bool Replace (tstring & lemma, TCHAR by, TCHAR what)
		{
			size_t i = lemma.find (what);

			if (i == tstring::npos) return false;

			// Орфографическая замена і на ї перед гласной
			if (by == _T('і') && IsVowelOrApostrophe (lemma [i-1]))
			{
				by = _T('ї');
			}

			lemma [i] = by;

			return true;
		}

		void Add (const tstring & lemma, const GramInfo & gramInfo)
		{
			entries.insert (std::pair <tstring, GramInfo> (lemma, gramInfo));
		}

		// Возвращает NULL, если такой статьи в words2ре нет.
		const GramInfo * Find (const tstring & s, RodChislo rodChislo) const
		{
			std::pair <Entries::const_iterator, Entries::const_iterator> r = entries.equal_range (s);

			for (Entries::const_iterator it = r.first; it != r.second; ++it)
			{
				if (it->second.GetRodChislo () == rodChislo) 
					return &(it->second);
			}

			return NULL;
		}

		// 0 - word не найдено
		// 1 - isMasculine
		// 2 - женский
		// 3 - isMasculine или женский
		int DetermineGender (const tstring & s) const
		{
			std::pair <Entries::const_iterator, Entries::const_iterator> r = entries.equal_range (s);

			int result = 0;

			for (Entries::const_iterator it = r.first; it != r.second; ++it)
			{
				result |= (1 << it->second.GetRodChislo ());
			}
		 
			return result;
		}
	};

	class Word
	{
		vector <tstring> endings;
		tstring lemmaStem;
		StemAlterations stemAlterations;

	public:
	
		Word (const tstring & lemma, const tstring & endings, StemAlterations stemAlterations)
			: endings (Split (endings))
			, lemmaStem (lemma.substr (0, lemma.length () - Split (endings) [0].length ()))
			, stemAlterations (stemAlterations)
		{
		}

		tstring GetFormByCase (int padeg, size_t wordNumber) const
		{
			enum {NOMINATIVE, GENITIVE, DATIVE, ACCUSATIVE, INSTRUMENTAL, LOCATIVE, VOCATIVE};

			if (lemmaStem == _T("олег") && padeg == VOCATIVE)
			{
				return _T("олеже");
			}
			
			tstring stem = this->lemmaStem;

			tstring ending = endings [padeg];

			// Орфографические замены перед гласной
			if (IsVowelOrApostrophe (*stem.rbegin ()) || *stem.rbegin () == _T('ь') || *stem.rbegin () == _T('й'))
			{
				ReplaceIf (ending, _T('і'), _T('ї'));
				ReplaceIf (ending, _T('е'), _T('є'));
			}

			tstring lemma = stem + endings [0];

			if (padeg == LOCATIVE && !ending.empty ())
			{
				if (EndsWithOneOf (lemma, _T("к,ко"))) ending = _T("у");

				if (EndsWithOneOf (lemma, _T("ун,ач,ар,яр,слав"))) ending = _T("і");
			}

			if (ending == _T("і"))
			{
				ApplyLastConsonantAlterations (stem);
			}

			if (BeginsWithVowel (ending) != BeginsWithVowel (endings [NOMINATIVE]))
			{
				if (stemAlterations.IO ()) 
				{
					ReplaceI (stem, _T('і'), _T('о')); 
				}
				else if (stemAlterations.IE ()) 
				{
					ReplaceI (stem, _T('і'), _T('е'));
					ReplaceI (stem, _T('ї'), _T('є'));
				}
			}

			if (padeg > 0)
			{
				if (EndsWith (lemma, _T("ів")) && lemma.length () > 2)
				{
					bool sibilant = tstring (_T("шщчжцрлн")).find (* ++ ++lemma.rbegin ()) != tstring::npos;
					ReplaceI (stem, _T('і'), sibilant ? _T('е') : _T('о'));
				}
				else if (EndsWith (lemma, _T("їв")))
				{
					ReplaceI (stem, _T('ї'), _T('є'));
				}

				// смешаное склонение - есть только у существительных
				if (stemAlterations.IsNoun () && tstring (_T("жшчщ")).find (*stem.rbegin ()) != tstring::npos) 
				{
					// § 47. А. 1. а)
					if (ending == _T("и")) // действует только на -и, но не на -им
					{
						ending = _T("і"); // кого? Ганжі
					}

					if (!ending.empty () && ending [0] == _T('о')) ending [0] = _T('е'); // мужские отчества
				}

				if (padeg == VOCATIVE && ending == _T("е"))
				{
					//http://linguist.univ.kiev.ua/WINS/pidruchn/imen/ilona2.htm#P_FIVE

					switch (*stem.rbegin ())
					{
						case _T('ч'): // Миколайовичу
						case _T('г'): // Олегу
						case _T('к'): // Костику
							ending = _T("у");
							break;
					}
				}

				if ((padeg == DATIVE || padeg == LOCATIVE) && EndsWith (lemma, _T("ко")) && !ending.empty ())
				{
					//ending = _T("у");
				}

				if (padeg == INSTRUMENTAL && EndsWithOneOf (lemma, _T("ар,яр")) && !ending.empty ())
				{
					//ending = _T("ем");
				}

				if (padeg == DATIVE && wordNumber == 0)
				{
					//if (ending == _T("у")) ending = _T("ові");
					//if (ending == _T("ю")) ending = _T("єві");
				}

				if (stemAlterations.StemSoftens ())
				{
					ReplaceIf (ending, _T ('а'), _T ('я'));
					ReplaceIf (ending, _T ('о'), _T ('е'));
					ReplaceIf (ending, _T ('у'), _T ('ю'));
					// Перед -Е произносится твердо: Ігорем (не Ігорєм)
				}

				if (stemAlterations.FleetingVowel () && !ending.empty ())
				{
					static tstring fleeting = tstring (_T("еєо"));

					tstring::iterator c = -- -- stem.end ();

					for (; c != stem.begin (); --c)
					{
						if (fleeting.find (*c) != tstring::npos) 
						{
							if (*c == _T('є'))
							{
								*c = _T('й');
							}
							else if (*(c-1) == _T('л'))
							{
								*c = _T('ь'); // Стілець - Стільця
							}
							else
							{
								stem.erase (c);
							}

							break;
						}
					}

				}
			}

			if (stem.length () >= 2 && ending == _T("'ю"))
			{
				// http://linguist.univ.kiev.ua/WINS/pidruchn/imen/f3.htm
				if (IsVowelOrApostrophe (*++stem.rbegin ()))
				{
					if (tstring (_T("бпвфмрщ")).find (*stem.rbegin ()) == tstring::npos)
					{
						*(ending.begin ()) = *stem.rbegin (); // вместо ' ставим последнюю согласную основы (Нинеллю)
					}
				}
				else
				{
					ending = _T("ю");
				}
			}

			if (EndsWithOneOf (stem, _T("ов,ев,єв")) && ending == _T("ові")) ending = _T("і"); // у Якові, у Києві
			if (EndsWithOneOf (stem, _T("ович,евич,євич")) && ending == _T("еві")) ending = _T("і"); 

			return stem + ending;
		}

	private:

		static bool BeginsWithVowel (const tstring & s)
		{
			return !s.empty () && IsVowel (*s.begin ());
		}

		static vector <tstring> Split (const tstring & s)
		{
			vector <tstring> result;

			size_t start = 0;

			while (start <= s.length ())
			{
				size_t next = s.find (' ', start);

				if (next == tstring::npos) next = s.length ();

				result.push_back (s.substr (start, next - start));

				start = next + 1;
			}

			return result;
		}

		static void ReplaceI (tstring & stem, TCHAR replaceWhat, TCHAR replaceBy)
		{
			size_t i = stem.find_last_of (replaceWhat);

			if (i == tstring::npos) return;

			stem [i] = replaceBy;
		}

		static void ApplyLastConsonantAlterations (tstring & stem)
		{
			Tokenizer t (_T("г/з к/ц х/с"), Tokenizer::Ukrainian);

			for (;;)
			{
				t.GetNextSeparator ();

				tstring replaceWhat = t.GetNextToken ();

				if (replaceWhat.empty ()) break;

				t.GetNextSeparator ();

				tstring replaceWith = t.GetNextToken ();

				if (EndsWith (stem, replaceWhat))
				{
					stem = stem.substr (0, stem.length () - replaceWhat.length ()) + replaceWith;
				}
			}
		}

		static void ReplaceIf (tstring & ending, TCHAR s1, TCHAR s2)
		{
			if (!ending.empty () && ending [0] == s1) ending [0] = s2;
		}
	};

	class Phrase
	{
		const vector <Word> words2;
		const vector <tstring> separators;
		const vector <Caps> caps; 
		const bool isMasculine;

	public:
	
		Phrase (
			const vector <Word> & words2, 
			const vector <tstring> & separators, 
			const vector <Caps> & caps,
			bool isMasculine)

			: words2 (words2)
			, separators (separators)
			, caps (caps)
			, isMasculine (isMasculine)
		{
		}

		bool IsMasculine () const
		{
			return isMasculine;
		}

		tstring GetFormByCase (int padeg) const
		{
			tstring sb;

			sb.append (separators [0]);

			for (size_t i=0; i < words2.size (); ++i)
			{
				tstring form = words2 [i].GetFormByCase (padeg, i);

				caps [i].Apply (form);

				sb.append (form);

				sb.append (separators [i+1]);
			}

			return sb;
		}
	};

	enum Gender {Gender_Fem = -1, Gender_Auto, Gender_Masc};

	class Morpher
	{
		Dictionary dictionary;

	public:
	
		Phrase Parse (const tstring & text, Gender gender) const
		{
			Tokenizer t (text, Tokenizer::Ukrainian);

			vector <tstring> separators;
			vector <tstring> words;
			vector <Caps> caps;

			int isMasculine = (int) gender;

			for (;;)
			{
				separators.push_back (t.GetNextSeparator ());

				tstring word = t.GetNextToken ();

				if (word.empty ()) break;

				if (! Tokenizer::ChangeLatinToCyrillic (&word))
				{
					t.PutBack (word);
					break;
				}

				caps.push_back (Caps::GetCaps (word));
				Caps::ToLower (word);
				if (gender == Gender_Auto) isMasculine += IsMasculine (word);
				words.push_back (word);
			}

			vector <Word> words2;
			words2.reserve(words.size());

			bool preposition = false;

			for (size_t i=0; i < words.size (); ++i)
			{
				StemAlterations stemAlterations;

				if (IsPreposition (words [i])) preposition = true;

				words2.push_back (Word (words [i], separators [i+1] == _T ("-") || preposition || separators [i+1].size () > 0 && separators [i+1][0] == _T('.') ? _T("      ") : GetEndings (words [i], isMasculine >= 0, &stemAlterations), stemAlterations));
			}

			return Phrase (words2, separators, caps, isMasculine >= 0);
		}

	private:

		int IsMasculine (const tstring & word) const
		{
			int mask = dictionary.DetermineGender (word);

			if (mask != 0) // слово есть в словаре
			{
				int result = 0;

				if (mask & 1) result += 2;
				if (mask & 2) result -= 2;

				return result;
			}

			// ЬКО - Романько, Синько
			if (EndsWithOneOf (word, _T("ук,юк,енко,єнко,ько,ець,єць"))) return 0;

			return EndsWithOneOf (word, _T("а,я")) ? -1 : 1;
		}

		tstring GetEndings (const tstring & word, bool isMasculine, StemAlterations * stemAlterations) const
		{
			const GramInfo * gi = dictionary.Find (word, isMasculine ? SingMasc : SingFem);

			int noun = gi ? (gi->IsNoun () ? 1 : -1) : 0;

			bool isAdjective;

			tstring endings = GetEndings (word, isMasculine, noun, &isAdjective);

			*stemAlterations = gi ? gi->GetStemAlterations () : StemAlterations (false, false, false, !isAdjective, EndsInEts (word));

			return endings;
		}

		static bool EndsInEts (const tstring & s)
		{
			tstring::const_reverse_iterator c = s.rbegin ();

			return s.length () > 4 && *c == _T('ь') && *++c == _T('ц') && (*++c == _T('е') || *c == _T('є')) && (IsVowelOrApostrophe (*++c) || IsVowelOrApostrophe (*++c));
		}

		static bool HasPossessiveSuffix (const tstring & s)
		{
			return EndsWithOneOf (s, _T("ов,ів,ев,єв,їв,ин,ін,їн"));
		}

		static bool IsPreposition (const tstring & word)
		{
			const TCHAR * a [] =
			{
				_T("ам"),
				_T("ан"),
				_T("од"), // Канселлo од Арнoне, Сан Генесіo од Уніта
				_T("оф"),
				_T("да"),
				_T("до"),
				_T("ла"),
				_T("де"),
				_T("ді"),
				_T("делла"), // Сан Гіoргіo делла Річінвелда
				_T("справ"), // Пoззуoлo справ Фріулі, Муззана справ Тургнанo
				_T("дель"), // Бассано дель Граппа
				_T("ин"), 
			};

			for (size_t i=0; i < sizeof (a) / sizeof (*a); ++i)
			{
				if (word == a [i]) return true;
			}

			return false;
		}

		static bool Uninflected (const tstring & word)
		{
			return IsParticle (word) || UninflectedFamilyNameEndingInIX (word);
		}

		static bool UninflectedFamilyNameEndingInIX (const tstring & word)
		{
			return EndsWithOneOf (word, _T("ських,цьких,зьких,еньких,них,ових"));
		}

		static bool IsParticle (const tstring & word)
		{
			const TCHAR * a [] =
			{
				_T("ра"),
				_T("ад"),
				_T("ас"),
				_T("ал"),
				_T("аль"),
				_T("ель"),
				_T("дер"),
				_T("лас"),
				_T("лос"),
				_T("анд"),
				_T("унд"),
				_T("сан"),
				_T("санта"), 
			};

			for (size_t i=0; i < sizeof (a) / sizeof (*a); ++i)
			{
				if (word == a [i]) return true;
			}

			return false;
		}

		static bool HasEndingFollowingConsonant (const tstring & word, const tstring & ending)
		{
			size_t i = word.length () - ending.length ();

			return i > 0 && !IsVowel (word [i-1]) && EndsWith (word, ending);
		}

		static bool InflectsO (const tstring & word)
		{
			return HasPossessiveSuffix (word.substr (0, word.size () - 1)) // Бабушкино
				|| HasEndingFollowingConsonant (word, _T("ко")) // Миколаєнко, Лук'янченко
				; 
		}

		tstring GetEndings (const tstring & word, bool isMasculine, int noun, bool * isAdjective) const
		{
			*isAdjective = false;

			if (Uninflected (word)) return _T("      ");

			if (word.length () == 1) return _T("      "); // quick hack for initials

			if (!isMasculine && noun != 0 /*найдено в словаре*/ && !EndsWithOneOf (word, _T("а,я"))) 
			{
				// 3 склонение
				return *word.rbegin () == _T('ь') 
					? _T("ь і і ь 'ю і е") // осінь
					: _T (" і і  'ю і е"); // любов, Сибир
			}

			if ((!isMasculine && noun == -1) || (noun != 1 && (EndsWithOneOf (word, _T("ова,іва,їва,ева,єва,іна,їна,ина,ська,цька,зька,чна")) && word.length () > 4 /*Рева,Нiна*/))) 
			{
				*isAdjective = true;

				return *word.rbegin () == _T('я') 
					? _T("я ьої ій ю ьою ій я") // синя
					: _T("а ої ій у ою ій а");
			}

			if (HasEnding (word, _T("а"))) return _T("а и і у ою і о");
			if (HasEnding (word, _T("я"))) return _T("я і і ю ею і е");

			if (!isMasculine) return _T("      ");

			// Закончили с женским родом, переходим к мужскому.

			if ((EndsWithOneOf (word, _T("ський,цький,зький,енький,ний,овий")) && (noun != 1)) || (noun == -1 && EndsWithOneOf (word, _T("ий,ій")))) 
			{
				*isAdjective = true;

				return *++word.rbegin () == _T('и') 
					?  _T("ий ого ому ого им ому ий")
					:  _T("ій ього ьому ього ім ьому ій");
			}

			if (noun != 1 && HasPossessiveSuffix (word)) return _T(" а у а им і е");

			//if (HasEnding (word, _T("ьо"))) return _T("ьо я ю я ьом і е"); // Иваньо
			if (HasEnding (word, _T("о")) && (noun != 0 || InflectsO(word))) return _T("о а у а ом і е"); // Петро, Дмитро
			if (HasEnding (word, _T("ь"))) return _T("ь я ю я ем і ю");
			if (HasEnding (word, _T("й"))) return _T("й я ю я єм і ю"); // Сергій 

			return IsVowelOrApostrophe (*word.rbegin ()) ? _T("      ") : _T(" а у а ом і е");
		}
	};
}


#pragma once

#include "../gc.h"
#include "../tchar.h"
#include "../tstring.h"
#include <string>

using std::tstring;

class RussianDeclensor;
class Phrase;

namespace Morpher
{
	namespace Russian
	{
		class Paradigm
		{
		protected:
			const Phrase * phrase;

		public:
			Paradigm (const Phrase * phrase);
			~Paradigm ();

		public:
			const tstring nominative     () const; // e.g. Иван,     Марья,   лес,    луг,     тень
			const tstring genitive       () const; // e.g. Ивана,    Марьи,   леса,   луга,    тени
			const tstring dative         () const; // e.g. Ивану,    Марье,   лесу,   лугу,    тени
			const tstring accusative     () const; // e.g. Ивана,    Марью,   лес,    луг,     тень
			const tstring instrumental   () const; // e.g. Иваном,   Марьей,  лесом,  лугом,   тенью
			const tstring prepositional  () const; // e.g. Иване,    Марье,   лесе,   луге,    тени
			const tstring prepositionalO () const; // e.g. об Иване, о Марье, о лесе, о луге,  о тени
			const tstring locative       () const; // e.g. в Иване,  в Марье, в лесу, на лугу, в тени

		private: // copy protection
			void operator = (const Paradigm &) {}
			Paradigm (const Paradigm &) {}
		};

		enum Gender
		{
			MASCULINE, FEMININE, NEUTER, PLURAL
		};

		class Parse : public Paradigm, public GC::Object
		{
			const Paradigm * _plural;

		public:
			Parse (const Phrase * phrase);
			~Parse ();

			const Paradigm * plural () const {return _plural;}

			Gender gender () const;
		};

		class Declension
		{
			const RussianDeclensor * const declensor;

		private: // copy protection
			void operator = (const Declension &) {}
			Declension (const Declension &)  : declensor (0) {}

		public:
			Declension (); // this can be time-consuming; don't put it in a loop
			~Declension ();

			GC::Ptr <Parse> parse (const tstring & input, int attributes = 0) const;

			const RussianDeclensor * get_declensor () const {return declensor;} // временный костыль
		};
	}
}

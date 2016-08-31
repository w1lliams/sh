#include "morpher-api-cpp.h"
#include "morpher.h"

namespace Morpher
{
	namespace Russian
	{
		Declension::Declension ()
			: declensor (new RussianDeclensor ())
		{
		}

		Declension::~Declension ()
		{
			delete declensor;
		}

		GC::Ptr <Parse> Declension::parse (const tstring & input, int attributes) const
		{
			Phrase * phrase = declensor->Analyse (input, (Attributes) attributes);

			return phrase ? new Russian::Parse (phrase) : 0;
		}

		Parse::Parse (const Phrase * phrase) 
			: Paradigm (phrase)
			, _plural (phrase->Plural () ? new Paradigm (phrase->Plural ()) : 0)
		{
		}

		Gender Parse::gender () const
		{
			switch (phrase->getRodChislo ())
			{
				case SingMasc: return MASCULINE;
				case SingFem : return FEMININE;
				case SingNeut: return NEUTER;
				case Plural:   return PLURAL;
				case RodChislo_Count: break;
			}

			throw "Bad RodChislo value.";
		}

		Parse::~Parse ()
		{
			if (_plural) delete _plural;
		}

		Paradigm::Paradigm (const Phrase * phrase)
			: phrase (phrase)
		{
		}

		Paradigm::~Paradigm ()
		{
			delete phrase;
		}

		const tstring Paradigm::nominative     () const { return phrase->getImen  (); }
		const tstring Paradigm::genitive       () const { return phrase->getRod   (); }
		const tstring Paradigm::dative         () const { return phrase->getDat   (); }
		const tstring Paradigm::accusative     () const { return phrase->getVin   (); }
		const tstring Paradigm::instrumental   () const { return phrase->getTvor  (); }
		const tstring Paradigm::prepositional  () const { return phrase->getPred  (); }
		const tstring Paradigm::prepositionalO () const { return phrase->getPredO (); }
		const tstring Paradigm::locative       () const { return phrase->getMest  (); }
	}
}

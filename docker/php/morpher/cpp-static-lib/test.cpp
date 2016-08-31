#include <stdio.h>

#include "../russian/morpher-api-cpp.h"

using namespace Morpher::Russian;

int main ()
{
	// This is your main Declension object. Constructing it currently takes around 200 ms and this time may vary in future versions.
	// You may want to construct it once at application start up or on first use.  Try to reuse it when possible.  Keep out of loops.
	// It also holds on to quite a bit of memory (~10MB) so it is up to you how long you want to keep it in scope.
	// It is stateless and can always be recreated at the expense of time.
	Declension declension;

	GC::Ptr <Parse> parse = declension.parse (_T("АТОМ"));

	if (parse->dative () != _T("АТОМУ"))
	{
		puts ("Incorrect dative.");
	}
	if (parse->locative () != _T("В АТОМЕ"))
	{
		puts ("Incorrect locative.");
	}
	if (declension.parse (_T("луг"))->locative () != _T("на лугу"))
	{
		puts ("Incorrect locative (2).");
	}
	if (declension.parse (_T("Нью-Йорк"))->accusative () != _T("Нью-Йорк"))
	{
		puts ("'New York' failed.");
	}
	if (declension.parse (_T("Бечвая Николози Омехиевич"))->accusative () != _T("Бечвая Николози Омехиевича"))
	{
		puts ("'Bechvaya' failed.");
	}
	if (declension.parse (_T("Стамбул"))->accusative () != _T("Стамбул")) puts ("'Stambul' failed.");
	if (declension.parse (_T("Далянь"))->genitive () != _T("Даляня")) puts ("'Dalian' failed.");
	if (parse->plural ()->nominative () != _T("АТОМЫ"))
	{
		puts ("Incorrect plural nominative.");
	}
	if (parse->gender () != MASCULINE)
	{
		puts ("Incorrect gender.");
	}
	if (declension.parse (_T("ЧЕРНЫШЕВА ЮЛИЯ"))->gender () != FEMININE)
	{
		puts ("Incorrect gender (All Caps).");
	}
	if (declension.parse (_T("ЧЕРНЫШЕВА ЮЛИЯ ВЛАДИМИРОВА"))->gender () != FEMININE)
	{
		puts ("Incorrect gender (All Caps) with patronymic.");
	}
	if (declension.parse (_T("NON-RUSSIAN")))
	{
		puts ("parse() should return NULL for non-Russian input.");
	}

	puts ("Press any key to exit ... ");

	getchar ();

	return 0;
}


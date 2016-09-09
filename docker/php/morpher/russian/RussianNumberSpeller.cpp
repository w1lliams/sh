#include "RussianNumberSpeller.h"

const TCHAR * RussianCaseSpeller::zeroToNine [] = 
    {0, 0, _T("два"), _T("три"), _T("четыре"), _T("пять"), _T("шесть"), _T("семь"), _T("восемь"), _T("девять")};

const TCHAR * RussianCaseSpeller::TwoThreeFourGenitivePrepositional [] = 
    {_T("двух"), _T("трёх"), _T("четырёх")};

const TCHAR * RussianCaseSpeller::TwoThreeFourDative [] = 
    {_T("двум"), _T("трём"), _T("четырём")};

const TCHAR * RussianCaseSpeller::TwoThreeFourInstrumental [] = 
    {_T("двумя"), _T("тремя"), _T("четырьмя")};

const TCHAR * RussianCaseSpeller::DvoeTroeChetvero [] = 
    {_T("двое"), _T("трое"), _T("четверо")};

const TCHAR * RussianCaseSpeller::DvestiTristaChetyresta [] = 
	{_T("двести"), _T("триста"), _T("четыреста")};

const TCHAR * RussianCaseSpeller::OdinEndings [] = {_T("ин"), _T("ного"), _T("ному"), _T("ин"), _T("ним"), _T("ном")};
const TCHAR * RussianCaseSpeller::OdnaEndings [] = {_T("на"), _T("ной"), _T("ной"), _T("ну"), _T("ной"), _T("ной")};
const TCHAR * RussianCaseSpeller::OdniEndings [] = {_T("ни"), _T("них"), _T("ним"), _T("ни"), _T("ними"), _T("них")};
const TCHAR * RussianCaseSpeller::StoIndirectEndings [] = {_T("от"), _T("от"), _T("там"), _T("от"), _T("тами"), _T("тах")};


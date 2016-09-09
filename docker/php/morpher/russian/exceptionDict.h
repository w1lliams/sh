#pragma once

#include "../tchar.h"

struct ExceptionForms
{
	const TCHAR * nominative;
	const TCHAR * genitive;
	const TCHAR * dative;
	const TCHAR * accusative;
	const TCHAR * instrumental;
	const TCHAR * prepositional;
	const TCHAR * locative;
};

struct Exception
{
	ExceptionForms singular, plural;
};

extern Exception exceptionDict [];
extern size_t exceptionDict_size;

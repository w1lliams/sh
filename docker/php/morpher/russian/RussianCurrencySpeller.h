#pragma once

#include "RussianNumberSpeller.h"

enum CurrencySpellErrorCode
{
	CurrencySpellErrorCode_Success,
	CurrencySpellErrorCode_NumberIsEmpty,
	CurrencySpellErrorCode_NumberIsNotANumber,
	CurrencySpellErrorCode_NumberIsTooBig,
	CurrencySpellErrorCode_NumberIsTooSmall,
	CurrencySpellErrorCode_DeclensionError,
	CurrencySpellErrorCode_RoundingNeeded,
};

const TCHAR * CurrencySpellErrorCodeToRussianTextMessage (CurrencySpellErrorCode errorCode);

struct CurrencySpellResult
{
	CurrencySpellResult (const tstring & result) : result (result), errorCode (CurrencySpellErrorCode_Success) {}
	CurrencySpellResult (CurrencySpellErrorCode errorCode) : result (_T("")), errorCode (errorCode) {}

	tstring result;
	CurrencySpellErrorCode errorCode;
};

class RussianCurrencySpeller
{
	const class AbbrMorpher * abbrMorpher;

	const RussianNumberSpeller numberSpeller;

	CurrencySpellResult Spell2(const tstring& number, const tstring& unit, int padeg) const;

public:
	RussianCurrencySpeller(const IMorpher* morpher);

	CurrencySpellResult Spell(double number, const tstring& unit, int padeg) const;

	CurrencySpellResult Spell(const tstring& number, const tstring& unit, int padeg) const;

	~RussianCurrencySpeller();
};

<?php

echo "Running tests...\n";

if (morpher_inflect("Пупкин Василий", "И") != "Пупкин Василий") echo "morpher_inflect (im rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "Р") != "Пупкина Василия") echo "morpher_inflect (rod rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "Д") != "Пупкину Василию") echo "morpher_inflect (dat rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "В") != "Пупкина Василия") echo "morpher_inflect (vin rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "Т") != "Пупкиным Василием") echo "morpher_inflect (tvor rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "П") != "Пупкине Василии") echo "morpher_inflect (predl rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "П_о") != "о Пупкине Василии") echo "morpher_inflect (predl-o rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "М") != "в Пупкине Василии") echo "morpher_inflect (gde rus) failed.\n";
if (morpher_inflect("Пупкин Василий", "im") != "Пупкин Василий") echo "morpher_inflect (im) failed.\n";
if (morpher_inflect("Пупкин Василий", "rod") != "Пупкина Василия") echo "morpher_inflect (rod) failed.\n";
if (morpher_inflect("Пупкин Василий", "dat") != "Пупкину Василию") echo "morpher_inflect (dat) failed.\n";
if (morpher_inflect("Пупкин Василий", "vin") != "Пупкина Василия") echo "morpher_inflect (vin) failed.\n";
if (morpher_inflect("Пупкин Василий", "tvor") != "Пупкиным Василием") echo "morpher_inflect (tvor) failed.\n";
if (morpher_inflect("Пупкин Василий", "predl") != "Пупкине Василии") echo "morpher_inflect (predl) failed.\n";
if (morpher_inflect("Пупкин Василий", "predl-o") != "о Пупкине Василии") echo "morpher_inflect (predl-o) failed.\n";
if (morpher_inflect("Пупкин Василий", "gde") != "в Пупкине Василии") echo "morpher_inflect (gde) failed.\n";
if (morpher_get_gender ("Василий Пупкин") != "m") echo "morpher_get_gender 'm' failed.\n";
if (morpher_get_gender ("Айгюн") != "f") echo "morpher_get_gender 'f' failed.\n";
if (morpher_get_gender ("молоко") != "n") echo "morpher_get_gender 'n' failed.\n";
if (morpher_get_gender ("Набережные Челны") != "p") echo "morpher_get_gender 'p' failed.\n";
if (morpher_inflect("name", "rod") != "#ERROR: Parameter 1 'text' is not Russian.") echo "Test #7 failed.\n";
if (morpher_inflect("", "tvor") != "#ERROR: Parameter 1 'text' should not be empty.") echo "Test #9 failed.\n";
if (morpher_inflect("Пупкин Василий", "jhl") != "#ERROR: Parameter 2 'case' is invalid.") echo "morpher_inflect: test for invalid case failed.\n";

if (morpher_inflect("кошка", "И mn") != "кошки") echo "Test 'koshki' (im rus mn) failed.\n";
if (morpher_inflect("кошка", "Р mn") != "кошек") echo "Test 'koshki' (rod rus mn) failed.\n";
if (morpher_inflect("кошка", "Д mn") != "кошкам") echo "Test 'koshki' (dat rus mn) failed.\n";
if (morpher_inflect("кошка", "В mn") != "кошек") echo "Test 'koshki' (vin rus mn) failed.\n";
if (morpher_inflect("кошка", "Т mn") != "кошками") echo "Test 'koshki' (tvor rus mn) failed.\n";
if (morpher_inflect("кошка", "П mn") != "кошках") echo "Test 'koshki' (predl rus mn) failed.\n";
if (morpher_inflect("кошка", "П_о mn") != "о кошках") echo "Test 'koshki' (predl-o rus mn) failed.\n";
if (morpher_inflect("кошка", "М mn") != "в кошках") echo "Test 'koshki' (gde rus mn) failed.\n";

if (morpher_inflect("кошка", "im mn") != "кошки") echo "Test 'koshki' (im mn) failed.\n";
if (morpher_inflect("кошка", "rod mn") != "кошек") echo "Test 'koshki' (rod mn) failed.\n";
if (morpher_inflect("кошка", "dat mn") != "кошкам") echo "Test 'koshki' (dat mn) failed.\n";
if (morpher_inflect("кошка", "vin mn") != "кошек") echo "Test 'koshki' (vin mn) failed.\n";
if (morpher_inflect("кошка", "tvor mn") != "кошками") echo "Test 'koshki' (tvor mn) failed.\n";
if (morpher_inflect("кошка", "predl mn") != "кошках") echo "Test 'koshki' (predl mn) failed.\n";
if (morpher_inflect("кошка", "predl-o mn") != "о кошках") echo "Test 'koshki' (predl-o mn) failed.\n";
if (morpher_inflect("кошка", "gde mn") != "в кошках") echo "Test 'koshki' (gde mn) failed.\n";

if (morpher_inflect("ножницы", "im mn") != "#ERROR: Parameter 1 'text' is plural.") echo "Test 'nozhnicy' failed.\n";
if (morpher_spell(123.56, "руб") != "Сто двадцать три руб 56 коп") echo "123 rub failed\n";

if (morpher_spell(123.56, "руб", "И") != "Сто двадцать три руб 56 коп") echo "123 rub (im rus) failed\n";
if (morpher_spell(123.56, "руб", "Р") != "Ста двадцати трёх руб 56 коп") echo "123 rub (rod rus) failed\n";
if (morpher_spell(123.56, "руб", "Д") != "Ста двадцати трём руб 56 коп") echo "123 rub (dat rus) failed\n";
if (morpher_spell(123.56, "руб", "В") != "Сто двадцать три руб 56 коп") echo "123 rub (vin rus) failed\n";
if (morpher_spell(123.56, "руб", "Т") != "Ста двадцатью тремя руб 56 коп") echo "123 rub (tvor rus) failed\n";
if (morpher_spell(123.56, "руб", "П") != "Ста двадцати трёх руб 56 коп") echo "123 rub (predl rus) failed\n";
if (morpher_spell(123.56, "руб", "П_о") != "#ERROR: Parameter 3 'case' is invalid.") echo "123 rub (predl-o rus) failed\n";
if (morpher_spell(123.56, "руб", "М") != "#ERROR: Parameter 3 'case' is invalid.") echo "123 rub (gde rus) failed\n";

if (morpher_spell(123.56, "руб", "im") != "Сто двадцать три руб 56 коп") echo "123 rub (im) failed\n";
if (morpher_spell(123.56, "руб", "rod") != "Ста двадцати трёх руб 56 коп") echo "123 rub (rod) failed\n";
if (morpher_spell(123.56, "руб", "dat") != "Ста двадцати трём руб 56 коп") echo "123 rub (dat) failed\n";
if (morpher_spell(123.56, "руб", "vin") != "Сто двадцать три руб 56 коп") echo "123 rub (vin) failed\n";
if (morpher_spell(123.56, "руб", "tvor") != "Ста двадцатью тремя руб 56 коп") echo "123 rub (tvor) failed\n";
if (morpher_spell(123.56, "руб", "predl") != "Ста двадцати трёх руб 56 коп") echo "123 rub (predl) failed\n";
if (morpher_spell(123.56, "руб", "predl-o") != "#ERROR: Parameter 3 'case' is invalid.") echo "123 rub (predl-o) failed\n";
if (morpher_spell(123.56, "руб", "gde") != "#ERROR: Parameter 3 'case' is invalid.") echo "123 rub (gde) failed\n";

if (morpher_spell("0.0123456789012345678901234567899", "процент") != "#ERROR: Parameter 1 'number' has more than 30 fraction digits.") echo "30 frac digits test failed\n";
if (morpher_spell("1234567890123456789012345678912", "процент") != "#ERROR: Parameter 1 'number' is too big.") echo "Big number test failed\n";

if (morpher_spell(123.567, "руб") != "#ERROR: Parameter 1 'number' needs rounding off to two decimal places.") echo "morpher_spell: 2 dp test failed\n";
if (morpher_spell("123.56", "руб") != "Сто двадцать три руб 56 коп") echo "123 (string) rub failed\n";
if (morpher_spell("", "руб") != "#ERROR: Parameter 1 'number' should not be empty.") echo "Test for empty number failed.";
if (morpher_spell(123, "") != "#ERROR: Parameter 2 'unit' should not be empty.") echo "Test for empty unit failed.";
if (morpher_spell(123, "кошка", "") != "#ERROR: Parameter 3 'case' should not be empty.") echo "Test for empty case failed.";
if (morpher_spell(38, "попугай", "dat") != "38 (Тридцати восьми) попугаям") echo "Test '38 popugaev' failed.";
if (morpher_spell(38, "popugai", "dat") != "#ERROR: Parameter 2 'unit' is not Russian or is missing the required form.") echo "morpher_spell: Test for non-Russian unit failed.";
if (morpher_spell(38, "мзда") != "#ERROR: Parameter 2 'unit' is not Russian or is missing the required form.") echo "morpher_spell: Test for missing form failed.";
if (morpher_spell("xyz", "собака") != "#ERROR: Parameter 1 'number' is not a number.") echo "morpher_spell: Test for bad number failed.";

if (morpher_spell(1.2, "процент") != "1.2 (Одна целая две десятых) процента") echo "1.2 per cent failed.";
if (morpher_spell("1,2", "процент") != "1,2 (Одна целая две десятых) процента") echo "1,2 per cent failed.";
if (morpher_spell(2, "сутки") != "2 (Двое) суток") echo "2 days failed.";
if (morpher_spell("100,00", "рубль") != "Сто рублей 00 копеек") echo "100,00 rub failed.";

if (morpher_ukr_inflect("Ганна", "Н") != "Ганна") echo "morpher_ukr_inflect (naz ukr) failed.\n";
if (morpher_ukr_inflect("Ганна", "Р") != "Ганни") echo "morpher_ukr_inflect (rod ukr) failed.\n";
if (morpher_ukr_inflect("Ганна", "Д") != "Ганні") echo "morpher_ukr_inflect (dav ukr) failed.\n";
if (morpher_ukr_inflect("Ганна", "З") != "Ганну") echo "morpher_ukr_inflect (zna ukr) failed.\n";
if (morpher_ukr_inflect("Ганна", "О") != "Ганною") echo "morpher_ukr_inflect (oru ukr) failed.\n";
if (morpher_ukr_inflect("Ганна", "М") != "Ганні") echo "morpher_ukr_inflect (mis ukr) failed.\n";
if (morpher_ukr_inflect("Ганна", "К") != "Ганно") echo "morpher_ukr_inflect (kly ukr) failed.\n";

if (morpher_ukr_inflect("Ганна", "naz") != "Ганна") echo "morpher_ukr_inflect (naz lat) failed.\n";
if (morpher_ukr_inflect("Ганна", "rod") != "Ганни") echo "morpher_ukr_inflect (rod lat) failed.\n";
if (morpher_ukr_inflect("Ганна", "dav") != "Ганні") echo "morpher_ukr_inflect (dav lat) failed.\n";
if (morpher_ukr_inflect("Ганна", "zna") != "Ганну") echo "morpher_ukr_inflect (zna lat) failed.\n";
if (morpher_ukr_inflect("Ганна", "oru") != "Ганною") echo "morpher_ukr_inflect (oru lat) failed.\n";
if (morpher_ukr_inflect("Ганна", "mis") != "Ганні") echo "morpher_ukr_inflect (mis lat) failed.\n";
if (morpher_ukr_inflect("Ганна", "kly") != "Ганно") echo "morpher_ukr_inflect (kly lat) failed.\n";

if (morpher_ukr_get_gender("Ганна") != "f") echo "morpher_ukr_get_gender (f) failed.\n";
if (morpher_ukr_get_gender("Микола") != "m") echo "morpher_ukr_get_gender (m) failed.\n";

echo "... done.\n";

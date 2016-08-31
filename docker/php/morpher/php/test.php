<?php

echo "Running tests...\n";

if (morpher_inflect("Пупкин Василий", "П_о") != "о Пупкине Василии") echo "Test #1 failed.\n";

if (morpher_get_gender ("роса") != "f") echo "Test #2 failed.\n";
if (morpher_get_gender ("Айгюн") != "f") echo "Test #3 failed.\n";
if (morpher_get_gender ("Чулпан") != "f") echo "Test #4 failed.\n";
if (morpher_get_gender ("Василий Пупкин") != "m") echo "Test #5 failed.\n";
if (morpher_get_gender ("Набережные Челны") != "p") echo "Test #6 failed.\n";
if (morpher_inflect("name", "rod") != "#ERROR: Parameter 1 'text' is not Russian.") echo "Test #7 failed.\n";
if (morpher_inflect("мир PHP", "Д") != "миру РНР") echo "Test #8 failed.\n";
if (morpher_inflect("", "tvor") != "#ERROR: Parameter 1 'text' should not be empty.") echo "Test #9 failed.\n";
if (morpher_inflect("Пупкин Василий", "jhl") != "#ERROR: Parameter 2 'case' is invalid.") echo "Test #10 failed.\n";
if (morpher_inflect("Нью-Йорк", "В") != "Нью-Йорка") echo "Test 'New York' failed.\n";
if (morpher_inflect("Куньмин", "В") != "Куньмин") echo "Test 'Kunming' failed.\n";

echo "... done.\n";

#!/bin/sh

POT="po/irm.pot"

# Extract the strings from all source files
xgettext -L PHP -k_ -k__ -o - $( find .. -name \*.php -or -name \*.inc ) | sed 's/charset=CHARSET/charset=iso-8859-1/' > $POT.extracted

# Merge changed strings into a new version of irm.pot
test -f $POT || touch $POT
mv $POT $POT~
msgmerge -o $POT $POT~ $POT.extracted && rm -f $POT.extracted $POT~

for file in po/*.po; do
	test -f $file || continue
	# Merge system messages into the per-language po file
	msgmerge -o $file.new $file $POT && mv $file.new $file

	# Compile the translations to an optimised form
	mo_dir="$( echo $file | sed 's%^po/\(.*\)\.po$%\1%')/LC_MESSAGES"
	mkdir -p $mo_dir
	msgfmt -o $mo_dir/irm.mo $file
done

#!/bin/bash
#
# Run this script from the root directory of irm.
# Example ./scripts/build_documentation.sh
#
# IRM should come with the latest documentation already compiled, but we can't promise it.
# This script will compile new documentation should you need it.

WHATSMYMOFONAME=`whoami`

# Put the location of your installed phpdoc here
PHPDOC_SOURCE="/home/$WHATSMYMOFONAME/sandbox/phpdocumentor-1.3.0rc3/phpdoc"

#Location of the documentation output
PHPDOC_OUTPUT='docs/code/'

#Currently phpdoc is only compatible with php4.
php4 $PHPDOC_SOURCE -dn "Information Resource Manager" -ti IRM irm -d include/,lib/,users/ -t $PHPDOC_OUTPUT

# The documentation will be available in a your web browser e.g. http://myhost/irm/docs/code/

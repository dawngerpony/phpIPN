#!/bin/sh
echo `pwd`
cd ..
pwd=`pwd`
cwd=`basename $pwd`
if [ $cwd != "prepay" ]
then
    echo "Not in correct directory (`pwd`), need to be in 'prepay' directory!"
    exit 1
fi

filename=prepay-`date +%s`.tar.gz
targets="admin/ notify.php templates/ include/"
tar -czf $filename $targets
echo "$filename created, containing: $targets"
ls -l $filename

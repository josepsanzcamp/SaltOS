#!/bin/bash

#~ http://127.0.0.1:8088/themeroller/images/ui-icons_123456_256x240.png

cd images
rm -f ui-icons_??????_256x240.png
cat ../jquery-ui.min.css | tr "\"" "\n" | grep images/ui-icons | sort -u | gawk '{print "wget http://127.0.0.1:8088/themeroller/"$0}' | sh
cd ..

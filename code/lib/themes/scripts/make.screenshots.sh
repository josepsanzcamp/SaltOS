#!/bin/bash

for i in */*/*/jquery-ui/jquery-ui.min.css */*/jquery-ui/jquery-ui.min.css; do
	if [ -f "${i}" ]; then
		echo $i
		j=$(echo $i|rev|cut -d/ -f3-|rev)
		if [ ! -f ${j}/preview.jpg ]; then
			echo $j
			wkhtmltoimage --format jpg --width 900 --height 600 "http://127.0.0.1:8089/?action=demo&style=${j}" ${j}/preview.jpg
		fi
	fi
done

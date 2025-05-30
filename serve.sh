#!/bin/bash
php -S localhost:8000 public/index.php 2>&1 | grep --line-buffered -v -E 'Accepted|Closing' | tee -a logs.txt

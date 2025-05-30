#!/bin/bash
echo -e "\e[31mWarning:\e[0m You're using PHP Development Server. This is NOT suitable for production."
php -S localhost:8000 public/index.php 2>&1 | grep --line-buffered -v -E 'Accepted|Closing' | tee -a logs.txt

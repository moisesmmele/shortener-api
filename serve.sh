#!/bin/bash

for arg in "$@"
do
  case $arg in
    --port=*)
    PORT="${arg#*=}"
    shift
    ;;
    *)
    ;;
  esac
done

PORT=${PORT:-8080}

echo -e "\e[31mWarning:\e[0m You're using PHP Development Server. This is NOT suitable for production."

php -S localhost:$PORT public/index.php 2>&1 | grep --line-buffered -v -E 'Accepted|Closing' | tee -a logs.txt

#!/bin/bash
php -S localhost:8000 public/index.php 2>&1 | grep -v -E 'Accepted|Closing'

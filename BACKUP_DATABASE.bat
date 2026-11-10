@echo off
chcp 65001 >nul
title پاشەکەوتکردنی داتابەیسی سیستەم (Database Backup)
color 0B
cls

echo ======================================================================
echo           سیستەمی دەفتەری ئۆتۆمبێل - دروستکردنی کۆپی یەدەگ
echo ======================================================================
echo.

if not exist "backups" mkdir "backups"

for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set dt=%%I
set TIMESTAMP=%dt:~0,4%-%dt:~4,2%-%dt:~6,2%_%dt:~8,2%-%dt:~10,2%

set BACKUP_FILE=backups\backup_%TIMESTAMP%.sqlite

if exist "database\database.sqlite" (
    copy "database\database.sqlite" "%BACKUP_FILE%" >nul
    echo [سەرکەوتوو بوو]
    echo کۆپی یەدەگی داتابەیس بە سەلامەتی پاشەکەوت کرا لە:
    echo %BACKUP_FILE%
) else (
    echo [هەڵە] فایلی داتابەیس database\database.sqlite نەدۆزرایەوە!
)

echo.
echo ======================================================================
echo تێبینی: دەتوانیت ئەم فایلانەی ناو فۆڵدەری backups کۆپی بکەیتە سەر فلاش مۆمۆری.
echo ======================================================================
pause

## [@MyAddressBookBot](https://telegram.me/myaddressbookbot)
https://img.shields.io/badge/status-running-brightgreen.svg?style=plastic https://img.shields.io/badge/build-1.0-green.svg?style=plastic https://img.shields.io/badge/license-GNU_AGPLv3-blue.svg?style=plastic

MyAddressBookBot is a Telegram Bot written in PHP 7.1 that consists in an address book of usernames for Telegram Messanger.

Telegram address book saves contacts by telephone number, but most users use the username to get in touch with people on this IM. This bot save contacts by username and let the user modify the basic info. Plus it update the username of contacts everyday so you'll never lose them.

# Features
- Add contacts by forward
- Add contacts manually
- Browse the address book using a inline keyboard
- Edit contact (first name, last name, description)
- Update username automatically or manually
- Search in the address book
- Choose order of contacts (first name or lastname)
- Share using inline queries
[screeshot](screenshot.png)

# Technical
This bot uses the official Telegram Bot API(v2.1) and runs on a NGINX 1.11.2 server offered by [Baum.xyz](http://baum.xyz/) with PHP 7.1, Redis.1 3.2 and Postgresql 9.3 installed:
- NGINX is used as the web server.
- PHP is the main language of the scripts.
- The Postgresql database is meant to save the contacts and the user that are using the bot.
- Redis is used to contains bot status and other volatile information, and it is also used as a cache containing language and contact order.

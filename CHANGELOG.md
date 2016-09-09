##v2.0.7
- Removed try/catch

##v2.0.6
- Added try/catch in adjustOffset.php
- Varius fixes

##v2.0.5
- The bot is now using getUpdatesLocal instead of getUpdatesRedis
- Bug fixes

## v2.0.4
- Now the contact description is shown only on contact info after selecting it
- Now the username in the contact description don't have italic formattation, letting the user click them on telegram message
- Bug fixes

## v2.0.3
- Bug fixes

## v2.0.2
- Bug fixes and use of long polling instead of webhook

## v2.0.1
- Bug fixes

# v2.0
- The bot is now based on HadesWrapper

# v1.01 - Bug fixes
- @username is now considered the same as @USERNAME
- Update username no longer insert "NoUsername" when the request getChat return "error_code 400: chat not found"
- "NoUsername" had been changed in "empty"

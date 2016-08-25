#!/usr/bin/env fish

while true
  php adjustOffset.php
  set error (redis-cli get error)
  if math "$error==1" > /dev/null
    redis-cli incr offset
  end
  php main.php
end

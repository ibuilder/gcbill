#!/bin/bash

echo "$(date): Starting run.sh script" >> server.log

echo "Checking if port 8081 is in use..." >> server.log
if lsof -i :8081 >> server.log 2>&1; then
  echo "Port 8081 is in use" >> server.log
  echo "Killing process on port 8081..." >> server.log
  if lsof -t -i :8081 | xargs kill >> server.log 2>&1; then
      echo "Process on port 8081 killed" >> server.log
    else
     echo "Failed to kill process on port 8081" >> server.log
   fi
else
  echo "Port 8081 is not in use" >> server.log
fi


if php -S localhost:8081 >> server.log 2>&1; then
  echo "Server running on http://localhost:8081" >> server.log

else
  echo "The port 8081 is already in use or the server failed to start. Please check the server.log file." >> server.log
  cat server.log
fi

echo "$(date): Finished run.sh script" >> server.log


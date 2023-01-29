
  -- Create the database
CREATE DATABASE circuit_board;

-- Use the circuit_board database
USE circuit_board;


-- Create the table for board status
CREATE TABLE board_status (
                              id INT(11) NOT NULL AUTO_INCREMENT,
                              board_id INT(11) NOT NULL,
                              switch_one ENUM('ON', 'OFF') NOT NULL,
                              switch_two ENUM('ON', 'OFF') NOT NULL,
                              switch_three ENUM('ON', 'OFF') NOT NULL,
                              switch_four ENUM('ON', 'OFF') NOT NULL,
                              fan ENUM('FORWARD', 'REVERSE') NOT NULL,
                              temperature INT(3) NOT NULL,
                              keypad INT(1) NOT NULL,
                              PRIMARY KEY (id),
);


-- Create a table to store the circuit board data
CREATE TABLE circuit_board (
                               id BINARY(16) NOT NULL,
                               switch_one ENUM('OFF', 'ON') NOT NULL,
                               switch_two ENUM('OFF', 'ON') NOT NULL,
                               switch_three ENUM('OFF', 'ON') NOT NULL,
                               switch_four ENUM('OFF', 'ON') NOT NULL,
                               fan ENUM('FORWARD', 'REVERSE') NOT NULL,
                               temperature INT NOT NULL,
                               keypad INT(1) NOT NULL,
                               created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                               updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               PRIMARY KEY (id)
);

-- Create a stored procedure to send the data to the M2M server via SMS
DELIMITER $$
CREATE PROCEDURE send_telemetry_data(IN board_id BINARY(16))
BEGIN
  -- Retrieve the data for the specified circuit board
SELECT switch_one, switch_two, switch_three, switch_four, fan, temperature, keypad
INTO @switch_one, @switch_two, @switch_three, @switch_four, @fan, @temperature, @keypad
FROM circuit_board
WHERE id = board_id;

-- Concatenate the data into a message string
SET @message = CONCAT(
    'Circuit board telemetry data:',
    ' Switch 1: ', @switch_one,
    ' Switch 2: ', @switch_two,
    ' Switch 3: ', @switch_three,
    ' Switch 4: ', @switch_four,
    ' Fan: ', @fan,
    ' Temperature: ', @temperature,
    ' Keypad: ', @keypad
  );

  -- Add a unique identifier to the message to identify it as coming from your team
  SET @message = CONCAT('[Team identifier] ', @message);

  -- Send the message to the M2M server via SMS
  -- Use the appropriate SMS client or the Send Message option in the EE SMS server interface
  -- Code to send SMS message goes here

  -- Log the message and the result of sending it
INSERT INTO sms_log (message, sent_at, result)
VALUES (@message, NOW(), @result);
END $$
DELIMITER ;


DROP TABLE if exists users;
CREATE TABLE users (
                       User_ID BINARY(16) NOT NULL,
                       Username VARCHAR(255) NOT NULL,
                       Email VARCHAR(255) NOT NULL,
                       Salt BINARY(16) NOT NULL,
                       Hash BINARY(64) NOT NULL,
                       Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       CONSTRAINT users_PK PRIMARY KEY (User_ID),
                       CONSTRAINT users_UK UNIQUE (Username, Email)
);



CREATE USER 'p2621996'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password' PASSWORD EXPIRE INTERVAL 180 DAY;
GRANT ALL PRIVILEGES ON telemetry.* TO 'p2621996'@'localhost' WITH GRANT OPTION;;

FLUSH PRIVILEGES;

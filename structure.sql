CREATE TYPE language AS ENUM('en', 'it', 'fr', 'de', 'ru', 'fa', 'hi');

CREATE TABLE "User" (
  "chat_id" int,
  "language" language DEFAULT 'en',
  "order" smallint DEFAULT 0,

  PRIMARY KEY ("chat_id")
);

CREATE TABLE "Contact" (
  "id" int,
  "username" VARCHAR(33),
  "first_name" VARCHAR(32),
  "last_name" VARCHAR(32),
  "desc" VARCHAR (50),
  "id_owner" int,
  "id_contact" bigint DEFAULT NULL,

  PRIMARY KEY ("id", "id_owner"),
  FOREIGN KEY ("id_owner") REFERENCES "User" ("chat_id")
);

\c postgres;

DROP TABLE IF EXISTS "taches";
CREATE TABLE "public"."taches" (
    "id" text NOT NULL,
    "texte" text NOT NULL,
    "accomplie" boolean NOT NULL,
    "date_ajout" text NOT NULL
) WITH (oids = false);

INSERT INTO "taches" ("id", "texte", "accomplie", "date_ajout") VALUES
('6376542bcaa53',	'finir le projet de nosql',	'0',	'2022-11-17 15:32:59'),
('6376543154534',	'apprécier la puissance de docker',	'0',	'2022-11-17 15:33:05'),
('6376543653b77',	'finir la présentation en anglais',	'0',	'2022-11-17 15:33:10');

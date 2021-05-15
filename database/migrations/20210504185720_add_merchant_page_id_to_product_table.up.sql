ALTER TABLE product ADD COLUMN IF NOT EXISTS merchant_page_id integer NOT NULL REFERENCES merchant_page;

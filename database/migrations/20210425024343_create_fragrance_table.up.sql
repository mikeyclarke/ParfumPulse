DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'fragrance_gender') THEN
        CREATE TYPE fragrance_gender AS ENUM ('male', 'female', 'unisex');
    END IF;
END
$$;

CREATE TABLE IF NOT EXISTS fragrance (
    id          integer generated BY DEFAULT AS IDENTITY PRIMARY KEY,
    name        text CHECK (LENGTH(name) <= 100) NOT NULL,
    url_slug    text CHECK (LENGTH(url_slug) <= 150) NOT NULL,
    url_id      text CHECK (LENGTH(url_id) <= 8) NOT NULL,
    gender      fragrance_gender DEFAULT NULL,
    brand_id    integer NOT NULL REFERENCES brand,
    created     TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated     TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_fragrance_updated_timestamp') THEN
        CREATE TRIGGER update_fragrance_updated_timestamp
        BEFORE UPDATE
        ON fragrance
        FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
    END IF;
END
$$;

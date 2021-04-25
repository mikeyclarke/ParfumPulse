CREATE TABLE IF NOT EXISTS variant (
    id              integer generated BY DEFAULT AS IDENTITY PRIMARY KEY,
    name            text CHECK (LENGTH(name) <= 100) NOT NULL,
    fragrance_id    integer NOT NULL REFERENCES fragrance,
    created         TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated         TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_variant_updated_timestamp') THEN
        CREATE TRIGGER update_variant_updated_timestamp
        BEFORE UPDATE
        ON variant
        FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
    END IF;
END
$$;

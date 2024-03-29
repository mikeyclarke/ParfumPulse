CREATE TABLE IF NOT EXISTS merchant (
    id          integer generated BY DEFAULT AS IDENTITY PRIMARY KEY,
    code        text CHECK (LENGTH(code) < 50) NOT NULL,
    name        text CHECK (LENGTH(name) <= 100) NOT NULL,
    created     TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated     TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_merchant_updated_timestamp') THEN
        CREATE TRIGGER update_merchant_updated_timestamp
        BEFORE UPDATE
        ON merchant
        FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
    END IF;
END
$$;

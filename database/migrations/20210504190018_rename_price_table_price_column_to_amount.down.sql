DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'price' and column_name='amount') THEN
        ALTER TABLE price RENAME COLUMN amount TO price;
    END IF;
END
$$;

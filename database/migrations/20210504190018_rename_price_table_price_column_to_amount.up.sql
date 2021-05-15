DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'price' and column_name='price') THEN
        ALTER TABLE price RENAME COLUMN price TO amount;
    END IF;
END
$$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'fragrance_type') THEN
        CREATE TYPE fragrance_type AS ENUM (
            'parfum',
            'eau de parfum',
            'eau de toilette',
            'eau_de_cologne',
            'eau fraiche',
            'aftershave water',
            'aftershave spray',
            'extrait de parfum'
        );
    END IF;
END
$$;

ALTER TABLE fragrance ADD COLUMN IF NOT EXISTS type fragrance_type NOT NULL;

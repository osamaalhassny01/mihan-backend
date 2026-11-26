-- Migration 020: Auto-sync provider services and locations mapping based on profession and city text

-- 1. Sync provider_services from profession text
INSERT IGNORE INTO provider_services (provider_id, service_id)
SELECT pp.id, s.id
FROM provider_profiles pp
JOIN services s ON (
    TRIM(s.name_ar) = TRIM(pp.profession)
    OR TRIM(pp.profession) LIKE CONCAT('%', TRIM(s.name_ar), '%')
    OR TRIM(s.name_ar) LIKE CONCAT('%', TRIM(pp.profession), '%')
)
WHERE pp.profession IS NOT NULL AND pp.profession != '';

-- 2. Sync provider_locations from city text
INSERT IGNORE INTO provider_locations (provider_id, location_id)
SELECT pp.id, l.id
FROM provider_profiles pp
JOIN locations l ON (
    TRIM(l.name_ar) = TRIM(pp.city)
    OR TRIM(pp.city) LIKE CONCAT('%', TRIM(l.name_ar), '%')
    OR TRIM(l.name_ar) LIKE CONCAT('%', TRIM(pp.city), '%')
)
WHERE pp.city IS NOT NULL AND pp.city != '';

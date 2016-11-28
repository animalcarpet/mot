-- Reset flag to false before mystery shopper epic will be turned ON on PROD
UPDATE vehicle
SET    is_incognito = FALSE
WHERE  is_incognito = TRUE;
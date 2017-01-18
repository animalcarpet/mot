-- bring all the anonymised data forward by the same amount of time, so that the latest mot_test was yesterday

SET @app_user_id = 1;

SET @max_completed_date = (SELECT DATE(MAX(completed_date)) FROM mot_test_current);
SET @yesterday = DATE_SUB(DATE(CURRENT_TIMESTAMP), INTERVAL 1 DAY);
SET @days_to_add = DATEDIFF(@yesterday, @max_completed_date);

UPDATE mot_test_current
SET
  started_date = DATE_ADD(started_date, INTERVAL @days_to_add DAY),
  completed_date = DATE_ADD(completed_date, INTERVAL @days_to_add DAY),
  submitted_date = DATE_ADD(submitted_date, INTERVAL @days_to_add DAY),
  issued_date = DATE_ADD(issued_date, INTERVAL @days_to_add DAY),
  expiry_date = DATE_ADD(expiry_date, INTERVAL @days_to_add DAY);

UPDATE mot_test_history
SET
  started_date = DATE_ADD(started_date, INTERVAL @days_to_add DAY),
  completed_date = DATE_ADD(completed_date, INTERVAL @days_to_add DAY),
  submitted_date = DATE_ADD(submitted_date, INTERVAL @days_to_add DAY),
  issued_date = DATE_ADD(issued_date, INTERVAL @days_to_add DAY),
  expiry_date = DATE_ADD(expiry_date, INTERVAL @days_to_add DAY);

-- remove the records triggered by the date changes
TRUNCATE mot_test_current_hist;
TRUNCATE mot_test_history_hist;

-- unset variables
SET @app_user_id = NULL;
SET @max_completed_date = NULL;
SET @yesterday = NULL;
SET @days_to_add = NULL;

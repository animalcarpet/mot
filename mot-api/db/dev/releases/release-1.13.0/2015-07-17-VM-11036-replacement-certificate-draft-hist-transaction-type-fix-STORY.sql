-- update transaction_type in history for deleted drafts
UPDATE replacement_certificate_draft_hist rcdh
SET rcdh.hist_transaction_type = 'D'
WHERE rcdh.hist_id IN (
  SELECT MAX(hist_id) AS max_hist_id
  FROM (SELECT
          hist_id,
          rcdh.id AS id
        FROM replacement_certificate_draft_hist rcdh
          LEFT JOIN replacement_certificate_draft rcd
            ON rcdh.id = rcd.id
        WHERE rcd.id IS NULL
       ) rcdj
  GROUP BY (id)
)
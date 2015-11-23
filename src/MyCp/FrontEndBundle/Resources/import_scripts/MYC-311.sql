SELECT count( b.booking_user_dates ) , b.booking_id, b.booking_user_id, b.booking_prepay, b.booking_user_dates, c.co_code, c.co_name
FROM booking b
  JOIN user u ON u.user_id = b.booking_user_id
  JOIN country c ON c.co_id = u.user_country
WHERE b.booking_id NOT
      IN (

  SELECT p.booking_id
  FROM payment p
)
GROUP BY b.booking_user_dates, booking_prepay;
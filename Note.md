Core logic (src/Services/) -> 1-9

1. Sensor distance to fill percentage

File: src/Services/ReadingService.php, function ingest(), around lines 21-24
What it does: the sensor sits on the lid, so a small distance means a full bin. The formula is (empty distance − measured distance) ÷ empty distance × 100, kept between 0 and 100.

2. False-reading filter (3 consecutive readings)

File: ReadingService.php, ingest(), around lines 26-28
What it does: it fetches the last 3 readings and only confirms an overflow if all 3 are at or above the threshold. The number 3 is CONFIRM_READINGS in .env.

3. Bin status decision

File: ReadingService.php, around lines 30-31
What it does: it sets the bin to needs_collection when confirmed, back to ok when below the threshold, and leaves a fault bin alone. Nothing currently sets fault.

4. Overflow alert (80%)

File: ReadingService.php, around line 40
What it does: it creates an alert when a bin is confirmed full. The threshold is FILL_ALERT_THRESHOLD in .env.

5. Low-battery alert (below 15%)

File: ReadingService.php, around line 42
What it does: it creates an alert when the battery is under the limit. The limit is LOW_BATTERY_THRESHOLD in .env.

6. No duplicate alerts

File: ReadingService.php, function alertOnce(), around line 48
What it does: it skips creating an alert if the same bin already has an open alert of the same type.

7. Route planning

File: src/Services/RouteService.php
build(), around line 14, collects the bins that need emptying and groups them by block, so 2 bins share one stop (around line 21).
orderStops(), around line 36, repeatedly picks the nearest unvisited stop and adds up the total distance.
haversine(), around line 55, works out the straight-line distance between two GPS points.

8. Driver confirms pickup

File: src/Services/CollectionService.php, function confirm()
What it does: it logs the collection, resets the bin to 0% and ok (around line 17), and resolves its overflow alert (around line 18).

9. Dashboard totals

File: src/Services/SummaryService.php, function dashboard()
What it does: it counts total bins, bins needing collection, faulty bins, the average fill and open alerts.

>Security Logic
10. API key check

File: src/Auth.php, function require(), around lines 8-20
What it does: it hashes the incoming key and looks it up. It returns 401 for a missing or invalid key and 403 if the role isn't allowed.

11. Which role can use which endpoint

File: each controller in src/Controllers/, on the Auth::require([...]) line
Roles: ReadingController allows only sensor, CollectionController allows only mobile, and the others allow mobile and web.

12. Key generation

File: scripts/generate_key.php
What it does: it creates a random 48-character key and stores only its hash.

>Input checks and routing
13. Input validation

File: src/Controllers/ReadingController.php, store()
What it checks: sensor_id and distance_cm are present and numeric, and battery_percent is between 0 and 100.
File: CollectionController.php
What it checks: bin_id and driver_name are present.

14. Endpoint list

File: public/index.php, the $r->add(...) lines
What it does: it maps each web address to a controller function.

**Not built yet*
>Median filtering of readings (the report asks for it).
>Marking a bin as fault, or detecting a sensor that has gone silent.
>Handling readings that arrive late, using their timestamp.
>Routing with the Google Distance Matrix API.

Changeable Settings
File: .env
Settings: FILL_ALERT_THRESHOLD, CONFIRM_READINGS, LOW_BATTERY_THRESHOLD, and DEPOT_LAT / DEPOT_LNG (the truck's starting point).
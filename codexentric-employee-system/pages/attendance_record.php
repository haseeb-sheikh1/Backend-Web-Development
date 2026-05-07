<?php
   
        session_start();
    if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

    $title     = "Attendance Record";
    $extra_css = "attendance_record";
    $current_page = "attendance_record";
    include_once "../includes/header.php";

    // ── Pull query params (set after form submit) ──
    $selected_employee_id = isset($_GET['employeeId']) ? (int)$_GET['employeeId'] : null;
    $selected_date        = isset($_GET['date'])       ? $_GET['date']           : date('Y-m-d');

    // ── Fetch employee list for dropdown ──
    // Replace with your actual DB connection / query
    $employees = [];
    /*
    $result = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM employees ORDER BY first_name");
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    */

    // ── Fetch attendance rows when an employee is selected ──
    $attendance_rows = [];
    $employee_name   = '';
    if ($selected_employee_id) {
        /*
        // Example query — adapt to your schema:
        $stmt = $conn->prepare("
            SELECT
                DATE(punch_in_time)                                         AS work_date,
                TIME(punch_in_time)                                         AS come_in_time,
                TIME(punch_out_time)                                        AS go_out_time,
                TIMEDIFF(punch_out_time, punch_in_time)                     AS duration
            FROM attendance
            WHERE employee_id = ?
              AND DATE(punch_in_time) = ?
            ORDER BY punch_in_time ASC
        ");
        $stmt->bind_param("is", $selected_employee_id, $selected_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $attendance_rows[] = $row;
        }

        // Get employee name
        $s2 = $conn->prepare("SELECT CONCAT(first_name,' ',last_name) AS full_name FROM employees WHERE id = ?");
        $s2->bind_param("i", $selected_employee_id);
        $s2->execute();
        $employee_name = $s2->get_result()->fetch_assoc()['full_name'] ?? '';
        */

        // ── DEMO DATA (remove once DB is wired up) ──
        $employee_name = "Demo Employee";
        $attendance_rows = [
            ['work_date' => $selected_date, 'come_in_time' => '09:02:00', 'go_out_time' => '13:00:00', 'duration' => '03:58:00'],
            ['work_date' => $selected_date, 'come_in_time' => '14:00:00', 'go_out_time' => '18:05:00', 'duration' => '04:05:00'],
        ];
    }

    // Helper: format H:i:s → H:i AM/PM
    function fmt_time($t) {
        if (!$t || $t === '00:00:00') return '—';
        return date('h:i A', strtotime($t));
    }
    // Helper: format duration H:i:s → Xh Ym
    function fmt_dur($t) {
        if (!$t || $t === '00:00:00') return '—';
        list($h, $m) = explode(':', $t);
        return ltrim($h, '0') . 'h ' . ltrim($m, '0') . 'm';
    }
?>

<style>
:root {
  --bg: #f1f5f9;
  --card-bg: #ffffff;
  --border: #e2e8f0;
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-orange: #ff7b1d;
  --brand-orange-hover: #e66a15;
  --brand-green: #186D55;
  --font-body: 'Nunito Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.dashboard-container {
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #1E6FD9;
    flex-shrink: 0;
}
.att-card-header h2 {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.att-card-body { padding: 24px; }

/* ── Search Form Grid ── */
.att-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 640px) { .att-form-grid { grid-template-columns: 1fr; } }

.att-form-group { display: flex; flex-direction: column; gap: 7px; }
.att-form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}
.att-form-group input,
.att-form-group select {
    height: 42px;
    padding: 0 12px;
    border: 1.5px solid #D1D5DB;
    border-radius: 7px;
    font-size: 13.5px;
    font-family: 'Inter', sans-serif;
    color: #111827;
    background: #fff;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    appearance: none; -webkit-appearance: none;
    cursor: pointer;
}
.att-form-group input:focus,
.att-form-group select:focus {
    border-color: #1E6FD9;
    box-shadow: 0 0 0 3px rgba(30,111,217,0.10);
}
.att-select-wrap { position: relative; }
.att-select-wrap::after {
    content: '';
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6B7280;
    pointer-events: none;
}
.att-select-wrap select { width: 100%; padding-right: 34px; }

.att-form-actions {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin-top: 4px;
}
.btn-view {
    height: 42px;
    padding: 0 24px;
    background: linear-gradient(135deg, #1559B5 0%, #1E6FD9 100%);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: background 0.18s, box-shadow 0.18s, transform 0.1s;
    box-shadow: 0 3px 10px rgba(21,89,181,0.25);
    white-space: nowrap;
}
.btn-view:hover {
    background: linear-gradient(135deg, #1248A0 0%, #1559B5 100%);
    box-shadow: 0 5px 16px rgba(21,89,181,0.35);
    transform: translateY(-1px);
}
.btn-reset {
    height: 42px;
    padding: 0 18px;
    background: #fff;
    color: #6B7280;
    border: 1.5px solid #D1D5DB;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: border-color 0.18s, color 0.18s;
    text-decoration: none;
    white-space: nowrap;
}
.btn-reset:hover { border-color: #1E6FD9; color: #1E6FD9; }

/* ── Results Section ── */
.att-results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}
.att-results-header .emp-tag {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}
.att-results-header .emp-tag .badge {
    background: #EBF2FC;
    color: #1559B5;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.att-date-tag {
    font-size: 13px;
    color: #6B7280;
    background: #F3F4F6;
    padding: 4px 12px;
    border-radius: 6px;
    font-weight: 500;
}

/* ── Table ── */
.att-table-wrap { overflow-x: auto; }
.att-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.att-table thead tr {
    background: #F8FAFC;
    border-bottom: 2px solid #E2E8F0;
}
.att-table thead th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    white-space: nowrap;
}
.att-table tbody tr {
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.12s;
}
.att-table tbody tr:last-child { border-bottom: none; }
.att-table tbody tr:hover { background: #F8FAFC; }
.att-table td {
    padding: 14px 16px;
    color: #374151;
    vertical-align: middle;
}

/* Time pill badges */
.time-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13.5px;
    font-weight: 600;
    color: #1559B5;
    background: #EBF2FC;
    padding: 4px 12px;
    border-radius: 20px;
}
.time-pill.out { color: #065F46; background: #D1FAE5; }
.time-pill.dur { color: #92400E; background: #FEF3C7; }
.time-pill svg { width: 13px; height: 13px; }

/* Empty state */
.att-empty {
    text-align: center;
    padding: 56px 24px;
    color: #9CA3AF;
}
.att-empty svg { margin-bottom: 16px; opacity: 0.4; }
.att-empty p { font-size: 14px; margin: 0; }
.att-empty strong { display: block; font-size: 16px; font-weight: 700; color: #6B7280; margin-bottom: 6px; }

/* Summary row */
.att-summary {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1.5px dashed #E2E8F0;
}
.att-summary-item {
    flex: 1;
    min-width: 130px;
    background: #F8FAFC;
    border-radius: 8px;
    padding: 14px 18px;
    border-left: 3px solid #1E6FD9;
}
.att-summary-item.total { border-left-color: #F59E0B; }
.att-summary-item label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #9CA3AF;
    display: block;
    margin-bottom: 5px;
}
.att-summary-item span {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    font-family: 'Inter', sans-serif;
}

/* Fade-in animation */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.att-card { animation: fadeUp 0.3s ease both; }
.att-card:nth-child(2) { animation-delay: 0.08s; }
</style>

                        >
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="att-form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-view">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        View
                    </button>
                    <a href="attendance_record.php" class="btn-reset">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Card (only shown after search) -->
    <?php if ($selected_employee_id): ?>
    <div class="att-card">
        <div class="att-card-header">
            <div class="icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <h2>Attendance Details</h2>
        </div>
        <div class="att-card-body">

            <!-- Results header -->
            <div class="att-results-header">
                <div class="emp-tag">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1559B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <?php echo htmlspecialchars($employee_name ?: 'Employee #' . $selected_employee_id); ?>
                    <span class="badge">ID: <?php echo $selected_employee_id; ?></span>
                </div>
                <span class="att-date-tag">
                    <?php echo date('D, d M Y', strtotime($selected_date)); ?>
                </span>
            </div>

            <?php if (!empty($attendance_rows)): ?>

                <!-- Table -->
                <div class="att-table-wrap">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Come In Time</th>
                                <th>Go Out Time</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_seconds = 0;
                            foreach ($attendance_rows as $i => $row):
                                // Accumulate total duration
                                if (!empty($row['duration']) && $row['duration'] !== '00:00:00') {
                                    list($dh, $dm, $ds) = explode(':', $row['duration']);
                                    $total_seconds += ($dh * 3600) + ($dm * 60) + $ds;
                                }
                            ?>
                            <tr>
                                <td style="color:#9CA3AF; font-size:13px;"><?php echo $i + 1; ?></td>
                                <td>
                                    <span class="time-pill">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        <?php echo fmt_time($row['come_in_time']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="time-pill out">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        <?php echo fmt_time($row['go_out_time']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="time-pill dur">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                                        </svg>
                                        <?php echo fmt_dur($row['duration']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Row -->
                <?php
                    $total_h = floor($total_seconds / 3600);
                    $total_m = floor(($total_seconds % 3600) / 60);
                    $first_in  = fmt_time($attendance_rows[0]['come_in_time']);
                    $last_out  = fmt_time(end($attendance_rows)['go_out_time']);
                ?>
                <div class="att-summary">
                    <div class="att-summary-item">
                        <label>First Check-In</label>
                        <span><?php echo $first_in; ?></span>
                    </div>
                    <div class="att-summary-item">
                        <label>Last Check-Out</label>
                        <span><?php echo $last_out; ?></span>
                    </div>
                    <div class="att-summary-item total">
                        <label>Total Duration</label>
                        <span><?php echo $total_h . 'h ' . $total_m . 'm'; ?></span>
                    </div>
                    <div class="att-summary-item">
                        <label>Sessions</label>
                        <span><?php echo count($attendance_rows); ?></span>
                    </div>
                </div>

            <?php else: ?>
                <!-- No Records -->
                <div class="att-empty">
                    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                    <strong>No Records Found</strong>
                    <p>No attendance entries for this employee on <?php echo date('d M Y', strtotime($selected_date)); ?>.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

</div>

<?php include_once "../includes/footer.php"; ?>
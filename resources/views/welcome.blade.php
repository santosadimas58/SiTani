<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Si Tani | Smart Irrigation Monitoring</title>
    <meta name="description" content="Si Tani helps growers monitor soil moisture, water flow, pH, temperature, and pump activity from one live dashboard.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --hw-bg: #071a1f;
            --hw-panel: rgba(8, 41, 48, 0.7);
            --hw-border: rgba(158, 240, 214, 0.16);
            --hw-text: #e9fff8;
            --hw-muted: #8eb7b3;
            --hw-teal: #63f5ca;
            --hw-cyan: #6ad8ff;
            --hw-gold: #ffd97c;
        }

        html {
            scroll-behavior: smooth;
        }

        body.hw-page {
            margin: 0;
            min-height: 100vh;
            color: var(--hw-text);
            background:
                radial-gradient(circle at top left, rgba(73, 189, 154, 0.18), transparent 28%),
                radial-gradient(circle at 85% 15%, rgba(106, 216, 255, 0.12), transparent 24%),
                linear-gradient(180deg, #071a1f 0%, #051318 100%);
            font-family: 'Manrope', sans-serif;
        }

        .hw-grid {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 88%);
        }

        .hw-shell {
            position: relative;
            z-index: 1;
            width: min(1160px, calc(100% - 32px));
            margin: 0 auto;
        }

        .hw-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0 12px;
        }

        .hw-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            color: inherit;
            text-decoration: none;
        }

        .hw-brand-mark {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background:
                linear-gradient(145deg, rgba(99, 245, 202, 0.3), rgba(106, 216, 255, 0.12)),
                rgba(255, 255, 255, 0.03);
            border: 1px solid var(--hw-border);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        }

        .hw-brand-copy strong,
        .hw-kicker,
        .hw-stat strong,
        .hw-card h3,
        .hw-stage-label,
        .hw-metric-label,
        .hw-section-title,
        .hw-footer-title {
            font-family: 'Space Grotesk', sans-serif;
        }

        .hw-brand-copy strong {
            display: block;
            font-size: 1.1rem;
            line-height: 1;
        }

        .hw-brand-copy span,
        .hw-chip,
        .hw-body,
        .hw-caption,
        .hw-list li,
        .hw-metric small {
            color: var(--hw-muted);
        }

        .hw-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hw-link,
        .hw-button {
            text-decoration: none;
            transition: transform 180ms ease, border-color 180ms ease, background 180ms ease;
        }

        .hw-link {
            color: var(--hw-text);
            font-size: 0.95rem;
        }

        .hw-link:hover,
        .hw-button:hover {
            transform: translateY(-1px);
        }

        .hw-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: 999px;
            border: 1px solid var(--hw-border);
            font-weight: 700;
            color: var(--hw-text);
        }

        .hw-button-primary {
            background: linear-gradient(135deg, var(--hw-teal), #32c7a5);
            color: #05211d;
            box-shadow: 0 18px 48px rgba(99, 245, 202, 0.24);
        }

        .hw-button-secondary {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
        }

        .hw-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: 32px;
            align-items: center;
            padding: 40px 0 80px;
        }

        .hw-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 1px solid var(--hw-border);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.03);
            font-size: 0.85rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .hw-kicker::before {
            content: "";
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: var(--hw-teal);
            box-shadow: 0 0 0 8px rgba(99, 245, 202, 0.12);
        }

        .hw-title {
            margin: 18px 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(3rem, 7vw, 5.9rem);
            line-height: 0.95;
            letter-spacing: -0.05em;
            max-width: 10ch;
        }

        .hw-title-accent {
            color: var(--hw-teal);
        }

        .hw-body {
            max-width: 58ch;
            font-size: 1.08rem;
            line-height: 1.75;
        }

        .hw-cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        .hw-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 32px;
        }

        .hw-stat,
        .hw-card,
        .hw-stage,
        .hw-signal,
        .hw-quote {
            background: var(--hw-panel);
            border: 1px solid var(--hw-border);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(14px);
        }

        .hw-stat {
            padding: 18px;
            border-radius: 22px;
        }

        .hw-stat strong {
            display: block;
            font-size: 1.75rem;
            margin-bottom: 6px;
        }

        .hw-visual {
            position: relative;
            min-height: 560px;
        }

        .hw-orb {
            position: absolute;
            inset: 0;
            margin: auto;
            width: 68%;
            height: 68%;
            border-radius: 999px;
            background:
                radial-gradient(circle at 30% 30%, rgba(99, 245, 202, 0.26), transparent 35%),
                radial-gradient(circle at 65% 65%, rgba(106, 216, 255, 0.2), transparent 38%),
                radial-gradient(circle, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
            filter: blur(10px);
            animation: float 7s ease-in-out infinite;
        }

        .hw-stage {
            position: relative;
            border-radius: 34px;
            padding: 22px;
            overflow: hidden;
        }

        .hw-stage::after {
            content: "";
            position: absolute;
            inset: auto -20% -35% auto;
            width: 240px;
            height: 240px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(99, 245, 202, 0.22), transparent 65%);
        }

        .hw-stage-head,
        .hw-stage-flow,
        .hw-stage-foot {
            position: relative;
            z-index: 1;
        }

        .hw-stage-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
        }

        .hw-stage-label {
            font-size: 1.4rem;
        }

        .hw-stage-flow {
            display: grid;
            gap: 14px;
        }

        .hw-signal {
            border-radius: 22px;
            padding: 16px;
        }

        .hw-signal-top,
        .hw-metric {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hw-meter {
            margin-top: 12px;
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }

        .hw-meter > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--hw-teal), var(--hw-cyan));
        }

        .hw-stage-foot {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .hw-metric {
            padding: 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .hw-section {
            padding: 20px 0 80px;
        }

        .hw-section-head {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: end;
            margin-bottom: 28px;
        }

        .hw-section-title {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.3rem);
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .hw-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .hw-card {
            border-radius: 28px;
            padding: 24px;
        }

        .hw-chip {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            font-size: 0.82rem;
            margin-bottom: 18px;
        }

        .hw-card h3 {
            margin: 0 0 12px;
            font-size: 1.35rem;
        }

        .hw-list {
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .hw-list li {
            position: relative;
            padding-left: 18px;
        }

        .hw-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.62em;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--hw-teal);
        }

        .hw-timeline {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            gap: 18px;
        }

        .hw-quote {
            border-radius: 32px;
            padding: 28px;
        }

        .hw-quote p {
            margin: 0;
            font-size: clamp(1.35rem, 3vw, 2.2rem);
            line-height: 1.25;
            letter-spacing: -0.03em;
        }

        .hw-quote footer {
            margin-top: 18px;
            color: var(--hw-muted);
        }

        .hw-footer {
            padding: 0 0 48px;
        }

        .hw-footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 28px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(99, 245, 202, 0.12), rgba(106, 216, 255, 0.07));
            border: 1px solid var(--hw-border);
        }

        .hw-footer-title {
            display: block;
            font-size: 1.45rem;
            margin-bottom: 6px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        @media (max-width: 980px) {
            .hw-hero,
            .hw-timeline,
            .hw-card-grid {
                grid-template-columns: 1fr;
            }

            .hw-section-head,
            .hw-footer-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .hw-visual {
                min-height: auto;
            }
        }

        @media (max-width: 720px) {
            .hw-shell {
                width: min(100% - 24px, 1160px);
            }

            .hw-nav {
                gap: 16px;
                flex-direction: column;
                align-items: stretch;
            }

            .hw-actions {
                justify-content: space-between;
            }

            .hw-stats,
            .hw-stage-foot {
                grid-template-columns: 1fr;
            }

            .hw-title {
                max-width: none;
            }
        }
    </style>
</head>
<body class="hw-page">
    <div class="hw-grid"></div>

    <div class="hw-shell">
        <header class="hw-nav">
            <a href="{{ route('home') }}" class="hw-brand">
                <div class="hw-brand-mark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2C8.6 6.2 6 9.3 6 13a6 6 0 0 0 12 0c0-3.7-2.6-6.8-6-11Z" fill="#63f5ca"/>
                        <path d="M12 8.4c-1.8 2.3-2.8 4-2.8 5.5A2.8 2.8 0 0 0 12 16.7a2.8 2.8 0 0 0 2.8-2.8c0-1.5-1-3.2-2.8-5.5Z" fill="#071a1f"/>
                    </svg>
                </div>
                <div class="hw-brand-copy">
                    <strong>Si Tani</strong>
                    <span>Smart irrigation command center</span>
                </div>
            </a>

            <div class="hw-actions">
                <a href="#features" class="hw-link">Features</a>
                <a href="#workflow" class="hw-link">Workflow</a>
                <a href="{{ route('login') }}" class="hw-button hw-button-secondary">Sign In</a>
            </div>
        </header>

        <main>
            <section class="hw-hero">
                <div>
                    <span class="hw-kicker">Realtime field telemetry</span>
                    <h1 class="hw-title">See water stress before it becomes <span class="hw-title-accent">yield loss.</span></h1>
                    <p class="hw-body">
                        Si Tani brings soil moisture, temperature, water pH, flow rate, and pump activity into one operational view so growers can react quickly, automate confidently, and irrigate with less waste.
                    </p>

                    <div class="hw-cta-row">
                        <a href="{{ route('login') }}" class="hw-button hw-button-primary">Open Dashboard</a>
                        <a href="#features" class="hw-button hw-button-secondary">Explore Platform</a>
                    </div>

                    <div class="hw-stats">
                        <div class="hw-stat">
                            <strong>5s</strong>
                            <span class="hw-caption">Live polling cadence for sensor visibility</span>
                        </div>
                        <div class="hw-stat">
                            <strong>4</strong>
                            <span class="hw-caption">Core water signals tracked per node</span>
                        </div>
                        <div class="hw-stat">
                            <strong>1</strong>
                            <span class="hw-caption">Control surface for monitoring and pump action</span>
                        </div>
                    </div>
                </div>

                <div class="hw-visual">
                    <div class="hw-orb"></div>
                    <div class="hw-stage">
                        <div class="hw-stage-head">
                            <div>
                                <div class="hw-stage-label">Field Node Overview</div>
                                <div class="hw-caption">North greenhouse irrigation lane</div>
                            </div>
                            <span class="hw-chip">Node active</span>
                        </div>

                        <div class="hw-stage-flow">
                            <div class="hw-signal">
                                <div class="hw-signal-top">
                                    <span class="hw-metric-label">Soil Moisture</span>
                                    <strong>68%</strong>
                                </div>
                                <div class="hw-meter"><span style="width: 68%"></span></div>
                            </div>

                            <div class="hw-signal">
                                <div class="hw-signal-top">
                                    <span class="hw-metric-label">Water Flow</span>
                                    <strong>14.2 L/m</strong>
                                </div>
                                <div class="hw-meter"><span style="width: 72%"></span></div>
                            </div>

                            <div class="hw-signal">
                                <div class="hw-signal-top">
                                    <span class="hw-metric-label">Water pH</span>
                                    <strong>6.8</strong>
                                </div>
                                <div class="hw-meter"><span style="width: 49%"></span></div>
                            </div>
                        </div>

                        <div class="hw-stage-foot">
                            <div class="hw-metric">
                                <div>
                                    <div class="hw-metric-label">Pump</div>
                                    <small>Current state</small>
                                </div>
                                <strong style="color: var(--hw-teal)">ON</strong>
                            </div>
                            <div class="hw-metric">
                                <div>
                                    <div class="hw-metric-label">Temperature</div>
                                    <small>Ambient reading</small>
                                </div>
                                <strong style="color: var(--hw-gold)">27°C</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="hw-section" id="features">
                <div class="hw-section-head">
                    <div>
                        <p class="hw-caption">Platform capabilities</p>
                        <h2 class="hw-section-title">Built for irrigation teams that need operational clarity, not another passive dashboard.</h2>
                    </div>
                    <p class="hw-body" style="max-width: 34ch;">
                        Si Tani already centers on nodes, sensor logs, live monitoring, and pump control. This landing page turns that product structure into a clear story.
                    </p>
                </div>

                <div class="hw-card-grid">
                    <article class="hw-card">
                        <span class="hw-chip">Live monitoring</span>
                        <h3>Track every node in one glance</h3>
                        <p class="hw-body">Watch soil moisture, temperature, pH, and water flow refresh continuously so issues surface before crops drift outside target conditions.</p>
                        <ul class="hw-list">
                            <li>Fast node-by-node signal checks</li>
                            <li>Clear active and offline status indicators</li>
                            <li>Realtime-friendly view for field operations</li>
                        </ul>
                    </article>

                    <article class="hw-card">
                        <span class="hw-chip">Control layer</span>
                        <h3>Switch pumps without leaving the dashboard</h3>
                        <p class="hw-body">Move from diagnosis to action immediately with direct pump control per node, reducing lag between detecting a problem and correcting flow.</p>
                        <ul class="hw-list">
                            <li>Simple ON and OFF state management</li>
                            <li>Per-node operational context</li>
                            <li>Useful for supervised semi-automation</li>
                        </ul>
                    </article>

                    <article class="hw-card">
                        <span class="hw-chip">Historical logs</span>
                        <h3>Review patterns instead of reacting blindly</h3>
                        <p class="hw-body">Use timestamped history to compare node behavior, verify irrigation decisions, and spot recurring anomalies across days or growing zones.</p>
                        <ul class="hw-list">
                            <li>Node and date-based filtering</li>
                            <li>Readable sensor timeline tables</li>
                            <li>Better traceability for field decisions</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="hw-section" id="workflow">
                <div class="hw-timeline">
                    <div class="hw-quote">
                        <p>Si Tani is strongest when the field team can observe, decide, and act from the same screen.</p>
                        <footer>Designed around node health, water behavior, and pump response.</footer>
                    </div>

                    <div class="hw-card-grid" style="grid-template-columns: 1fr; gap: 18px;">
                        <article class="hw-card">
                            <span class="hw-chip">01 Observe</span>
                            <h3>Read the field in realtime</h3>
                            <p class="hw-body">Check which nodes are healthy, which readings are drifting, and whether fresh telemetry is still arriving on schedule.</p>
                        </article>
                        <article class="hw-card">
                            <span class="hw-chip">02 Verify</span>
                            <h3>Compare with recent history</h3>
                            <p class="hw-body">Confirm whether the issue is transient or recurring before changing irrigation behavior across a zone.</p>
                        </article>
                        <article class="hw-card">
                            <span class="hw-chip">03 Respond</span>
                            <h3>Adjust pump activity directly</h3>
                            <p class="hw-body">Execute the operational response where the telemetry is already visible, without context switching to another tool.</p>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <footer class="hw-footer">
            <div class="hw-footer-inner">
                <div>
                    <span class="hw-footer-title">Si Tani keeps irrigation decisions measurable.</span>
                    <span class="hw-caption">Start from the dashboard, monitor every node, and keep water movement intentional.</span>
                </div>
                <a href="{{ route('login') }}" class="hw-button hw-button-primary">Launch Si Tani</a>
            </div>
        </footer>
    </div>
</body>
</html>

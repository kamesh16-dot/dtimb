<?php
// Product page content, transcribed from the TIMB spec sheets (see tools/). Edit freely.
// Strings in 'features', 'items' may contain <strong>; everything else is escaped on output.
return [
    "draw-bench" => [
        "slug" => 'draw-bench',
        "tag" => 'Draw Bench',
        "title" => [
            'Draw',
            'Bench',
            '',
        ],
        "tagline" => 'Full line of motorized chain-type draw benches for straight length die-drawing of round rod and tube. Single or multiple pulling configurations.',
        "hero" => 'product-draw-bench/hero',
        "heroCaption" => [
            'TDB-Series Chain-Type Draw Bench',
            'Full-line engineering layout — carriage bed, multi-strand pulling rails & drive-end gearbox',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Capacity Range',
                '5T – 250T',
            ],
            [
                'Configuration',
                'Single / Three',
            ],
            [
                'Chain',
                'TIMB Proprietary',
            ],
            [
                'Lubrication',
                'Dry & Wet',
            ],
        ],
        "info" => [
        ],
        "callout" => null,
        "features" => [
            "title" => 'Key Features',
            "items" => [
                'Specifically designed &amp; developed chain and gearbox manufactured in-house with proprietary technology',
                'Spherical die holder system to adjust tube bend for better accuracy',
                'Draw Pull Force Measurement and Readout for process control',
                'Manual or Hydraulically Actuated Grippers with removable tooling for quick size changes',
                'Precision guiding system for cold drawing profiled tubes',
                'Variable wall thickness tube capability',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => 'Gripper & Die Holder System',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-draw-bench/mech-1',
                        "title" => 'Secure clamping, quick change-over',
                        "items" => [
                            'Pneumatically actuated gripper jaws clamp the workpiece securely for high-force pulling',
                            'Removable, interchangeable tooling allows quick change-over between tube and rod sizes',
                            'Spherical die holder self-aligns to correct minor bend, improving dimensional accuracy',
                            'Precision linear guide rails keep the carriage tracking true along the full draw length',
                        ],
                    ],
                ],
            ],
            [
                "title" => 'Chain Drive & Pull-Head Assembly',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-draw-bench/mech-2',
                        "title" => 'Proprietary chain, measured pull force',
                        "items" => [
                            'Proprietary in-house chain and gearbox deliver smooth, high-force pulling with minimal wear',
                            'Entry roller station guides and aligns incoming stock before it enters the die',
                            'Draw pull force is measured and displayed in real time for process control',
                            'Robust welded steel frame built for continuous heavy-duty operation',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Applications',
                "chips" => [
                    'Front Fork',
                    'Stabilizer Bar',
                    'Drive Shaft',
                    'Axle Tube',
                    'Shock Absorber',
                    'Hydraulic Cylinder',
                    'PTO Tube',
                    'Automotive Tubes',
                    'Precision Seamless Tubes',
                ],
            ],
        ],
        "galleries" => [
            [
                "img" => 'product-draw-bench/gallery-1',
                "title" => 'Complete Line Engineering',
                "text" => 'Every TDB-Series draw bench is engineered end-to-end — infeed roller bed, guarded carriage rails, chain-drive pull unit and gearbox — for continuous, high-volume cold drawing.',
            ],
        ],
        "highlight" => [
            "label" => 'Builds a complete cold-drawing line',
            "text" => 'Draw Bench works best when paired with the Push Pointing Machine (pre-process) and the Straightening Machine (post-process) for a complete cold drawing line solution.',
            "pairs" => [
                'push-pointing',
                'straightening',
            ],
        ],
        "cta" => 'Enquire about your complete cold drawing line:',
    ],
    "hydrotester" => [
        "slug" => 'hydrotester',
        "tag" => 'Hydrotester',
        "title" => [
            '',
            'Hydrotester',
            '',
        ],
        "tagline" => 'High-pressure hydrostatic leak & strength testing system for tubes, pipes, valves, cylinders and fabricated components — built with a robust structure and controlled intensifier system for safe, repeatable performance in production or standalone quality validation.',
        "hero" => 'product-hydrotester/hero',
        "heroCaption" => [
            'HT-Series Hydrotester',
            'Complete hydrostatic test line with roller conveyor, accumulator, hydraulic power pack & PLC-HMI control console',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Max Pressure',
                'Up to 600 Bar',
            ],
            [
                'Test Medium',
                'Water',
            ],
            [
                'Control',
                'PLC + HMI',
            ],
            [
                'Automation',
                'OK/NG Auto Sort',
            ],
        ],
        "info" => [
            [
                'Automatic Test Cycle',
                [
                    'Filling & Pressurization → Holding at Test Pressure',
                    'Controlled Decompression → Automatic OK/NG Segregation',
                ],
            ],
        ],
        "callout" => [
            'Pressure Variants',
            'Standard: up to 350 Bar · High Pressure: up to 600 Bar — customizable range per application',
        ],
        "features" => [
            "title" => 'Salient Features',
            "items" => [
                'High-pressure testing capability with a customizable pressure range',
                'PLC + HMI controlled automatic test cycle for consistent, repeatable results',
                'Accurate pressure intensifier system for precise, controlled build-up',
                'Stable pressure hold with minimal drop throughout the test cycle',
                'Automatic leak detection with clear pass/fail (OK/NG) indication',
                'Digital pressure monitoring with optional data logging for traceability',
                'Safety interlocks with automatic pressure relief system',
                'Modular design adaptable to different product sizes',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => 'Machine Configurations',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-hydrotester/mech-1',
                        "title" => 'Integrated Handling & Control Line',
                        "items" => [
                            'Compact pressure intensifier module paired with accumulator and hydraulic power pack',
                            'PLC + HMI control console drives the entire automatic test sequence',
                            'Sealing heads and hydraulic clamping cylinders positioned at the test station',
                            'Compact footprint suited to standalone cells or in-line production integration',
                        ],
                    ],
                    [
                        "num" => '',
                        "img" => 'product-hydrotester/mech-2',
                        "title" => 'Long-Length & Large-Diameter Test Line',
                        "items" => [
                            'Pit-mounted rail bed accommodates long-length pipes and heavy oilfield tubulars',
                            'Overhead gantry carries the hydraulic clamping &amp; sealing head assembly along the line',
                            'Elevated glass-walled control cabin gives operators a full view of the test line',
                            'Twin accumulator tanks and pump skid maintain consistent pressure across long test runs',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Target Customers',
                "chips" => [
                    'Seamless & ERW Pipe Manufacturers',
                    'Hydraulic Tube Manufacturers',
                    'Heat Exchanger Manufacturers',
                    'Oil & Gas Equipment Manufacturers',
                ],
            ],
        ],
        "galleries" => [
        ],
        "highlight" => [
            "label" => 'Complete quality assurance',
            "text" => 'Every seamless, ERW and hydraulic tube manufacturer needs pressure-integrity verification before dispatch. Pair the Hydrotester with your TIMB Tube Mill, Draw Bench or Pilger Mill for a complete, in-house testing and production line.',
            "pairs" => [
                'tube-mill',
                'draw-bench',
                'pilger-mill',
            ],
        ],
        "cta" => 'Ensure tube & pipe quality with TIMB\'s testing solution:',
    ],
    "pilger-mill" => [
        "slug" => 'pilger-mill',
        "tag" => 'Pilger Mill',
        "title" => [
            'Pilger',
            'Mill',
            '',
        ],
        "tagline" => 'Advanced cold pilgering technology for high-precision seamless tubes. Reduces diameter and wall thickness simultaneously in a single step — superior roundness, surface finish and material homogeneity.',
        "hero" => 'product-pilger-mill/hero',
        "heroCaption" => [
            'HPTR-Series Pilger Mill',
            'Reciprocating cold-pilgering line with servo-controlled feed & rotation',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Ingoing Tube',
                'Ø12–Ø22 mm',
            ],
            [
                'Outgoing Tube',
                'Ø10–Ø20 mm',
            ],
            [
                'Speed',
                '160 strokes/min',
            ],
            [
                'OD Tolerance',
                '±0.03 mm',
            ],
        ],
        "info" => [
            [
                'Materials Processed',
                [
                    'Mild Steel · Stainless Steel · Ferritic Steel',
                    'Copper & Copper Alloys · Titanium Alloys',
                    'Zirconium & Nickel Alloys',
                ],
            ],
        ],
        "callout" => [
            'Variants Available',
            '2 Roll Pilger Mill · 3 Roll HPTR Mill (Dual Strand)',
        ],
        "features" => [
            "title" => 'Why TIMB Pilger Mills?',
            "items" => [
                'Servo-controlled feeding &amp; rotation with step-less adjustment for precision accuracy',
                '<strong>Dual strand machine</strong> — two tubes reduced simultaneously for higher output',
                'High reduction per pass with superior dimensional accuracy — OD &amp; wall tolerance of ±0.03mm',
                'Excellent surface finish and enhanced mechanical properties vs conventional drawing',
                'Suitable for difficult-to-work materials — stainless steel, nickel, titanium &amp; alloys',
                'High material utilisation with consistent, repeatable tube quality',
                'Servo-controlled mandrel rotation for enhanced process control',
                'Engineered for critical, high-value tube applications',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => 'How Pilger Rolling Works',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-pilger-mill/mech-1',
                        "title" => 'Reciprocating rolls, incremental feed & rotation',
                        "items" => [
                            'A reciprocating roll cassette moves back and forth over tapered die grooves, progressively reducing diameter &amp; wall thickness',
                            'The tube is incrementally rotated and fed forward between strokes for uniform, all-around reduction',
                            'Precision guide rails keep the roll carriage tracking accurately at up to 160 strokes per minute',
                            'Result: superior roundness, surface finish &amp; material grain structure vs conventional drawing',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Materials & Tube Types',
                "chips" => [
                    'Precision Seamless Tubes',
                    'Stainless Steel Tubes',
                    'Nickel & Nickel Alloys',
                    'Titanium Alloys',
                    'Heat Exchanger Tubes',
                    'Boiler & Power Tubes',
                    'Hydraulic & Instrumentation Tubes',
                    'Aerospace Tubes',
                    'Nuclear Industry Tubes',
                    'Medical Tubes',
                ],
            ],
            [
                "title" => 'Industries Served',
                "chips" => [
                    'Medical',
                    'Aviation',
                    'Nuclear',
                    'Heat Exchanger',
                    'Chemical',
                    'Refrigerators',
                    'Food Industry',
                    'Automotive',
                    'Aerospace',
                ],
            ],
        ],
        "galleries" => [
            [
                "img" => 'product-pilger-mill/gallery-1',
                "title" => 'In-Line Production Setup',
                "text" => 'TIMB Pilger Mills run as fully integrated lines with dedicated hydraulic power packs and PLC control — shown here in active production.',
            ],
        ],
        "highlight" => [
            "label" => 'TIMB value proposition',
            "text" => 'Engineered for high reduction, built for precision, designed for critical tube applications.',
            "chips" => [
                'High Reduction',
                'Precision',
                'Productivity',
                'Reliability',
            ],
        ],
        "cta" => 'From Engineering to Commissioning — Complete Pilger Mill Solutions. Discuss your precision tube forming requirements:',
    ],
    "press-entry-line" => [
        "slug" => 'press-entry-line',
        "tag" => 'Press Entry Line · Model 700-T',
        "title" => [
            'Press Entry',
            'Line',
            '',
        ],
        "tagline" => 'Precision coil feeding system engineered for fine blanking presses. Decoils, straightens and feeds strip stock with the pitch accuracy and repeatability demanded for high-volume, close-tolerance blanking — including drive chain and automotive components.',
        "hero" => 'product-press-entry-line/hero',
        "heroCaption" => [
            '700-T Press Entry Line',
            'Guarded coil-to-press feed line — decoiler, straightener & servo feed for high-precision fine blanking',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Coil OD',
                'Ø1800 mm',
            ],
            [
                'Coil Thickness',
                '2–10.5 mm',
            ],
            [
                'Coil Width',
                '55–360 mm',
            ],
            [
                'Coil Speed',
                '5–16 m/min',
            ],
        ],
        "info" => [
            [
                'Coil Handling Capacity',
                [
                    'Coil ID: Ø505 mm · Coil OD: Ø1800 mm',
                    'Coil Weight: Up to 3000 Kg',
                    'Model: 700-T',
                ],
            ],
        ],
        "callout" => [
            'Power & Drive',
            'Pneumatic Pressure: 5 Bar · Hydraulic Pressure: 100 Bar · Power Source: 3-Phase, 50Hz, 42kVA',
        ],
        "features" => [
            "title" => 'Why TIMB Press Entry Line?',
            "items" => [
                '<strong>Precision strip feeding</strong> — consistent pitch accuracy critical to fine blanking tolerances',
                '<strong>Heavy coil capacity</strong> — handles coils up to 3000 Kg and Ø1800mm OD for extended, uninterrupted runs',
                'Wide material range — 2–10.5mm thickness and 55–360mm width covers diverse chain &amp; component profiles',
                'Integrated hydraulic &amp; pneumatic control — 100 Bar hydraulic clamping with 5 Bar pneumatic assist for stable, repeatable feeding',
                'High-speed capability up to 16 m/min, matched to modern high-tonnage press cycle rates',
                'Robust industrial-duty construction engineered for continuous, high-volume production',
                'Fast coil changeover minimizes press downtime and maximizes line utilisation',
                'Custom-engineered to match specific press bed height, tonnage &amp; tooling requirements',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => 'Complete Coil-to-Press Feed Line',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-press-entry-line/mech-1',
                        "title" => 'Decoiler, Straightener & Servo Feed',
                        "items" => [
                            'Motorized decoiler with mandrel expansion holds coils up to Ø1800mm OD / 3000Kg, paying off strip under controlled tension',
                            'Precision roller straightener removes coil-set, camber &amp; cross-bow for dead-flat strip entering the press',
                            'Hydraulic clamping (100 Bar) with pneumatic assist (5 Bar) holds strip position with zero slip during high-speed feed cycles',
                            'Fully guarded, PLC-integrated line synchronizes feed pitch with press stroke for consistent, high-tolerance fine blanking',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Applications',
                "chips" => [
                    'Drive Chain Link Plates',
                    'Two & Three-Wheeler Chain Components',
                    'Automotive Transmission Chain Parts',
                    'Industrial Roller Chain Plates',
                    'Fine Blanked Automotive Stampings',
                    'Precision Sprocket Blanks',
                    'Seat Belt & Safety Components',
                    'Electric Motor Laminations',
                    'General Precision Stamping Lines',
                ],
            ],
        ],
        "galleries" => [
        ],
        "highlight" => [
            "label" => 'Why TIMB Press Entry Line?',
            "text" => '',
            "chips" => [
                'Precision Feeding',
                'Heavy Coil Capacity',
                'High-Speed Performance',
                'Robust Construction',
                'Reduced Press Downtime',
                'Application-Specific Engineering',
            ],
        ],
        "cta" => 'Ready to discuss your coil feeding requirement for fine blanking or precision stamping?',
    ],
    "push-pointing" => [
        "slug" => 'push-pointing',
        "tag" => 'Push Pointing Machine',
        "title" => [
            'Push',
            'Pointing',
            'Machine',
        ],
        "tagline" => 'Our Push Pointer Machines are engineered for heavy-duty end pointing of rods, tubes, pipes, and solid cylindrical components. With proven hydraulic designs ranging from 35 Tons to 350 Tons capacity, the system delivers controlled and uniform diameter reduction for downstream processing. The rigid frame construction ensures stability under high loads, making it suitable for demanding industrial environments. Designed for precision, durability, and consistent performance.',
        "hero" => 'product-push-pointing/hero',
        "heroCaption" => [
            'Push Pointer Machine',
            'Hydraulic pointing unit with integrated power pack & control cabinet',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Capacity Range',
                '35T – 350T',
            ],
            [
                'Die System',
                '3-Stage Index',
            ],
            [
                'Operation',
                'Hydraulic',
            ],
            [
                'Material',
                'Ferrous & NF',
            ],
        ],
        "info" => [
        ],
        "callout" => null,
        "features" => [
            "title" => 'Key Features',
            "items" => [
                'Hydraulically operated — far superior ease of pointing vs conventional swaging/squeeze',
                'Three Stage Die Indexing system for small diameter thick wall tubes',
                'Supplied with suitable handling system for smooth material flow',
                'Load sensing hydraulic pump for low power consumption',
                'Regenerative hydraulic circuits for faster cycle time',
                'Optional: IIoT features, auto/semi-auto handling, online lubrication system',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => '3-Stage Die Indexing System',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-push-pointing/mech-1',
                        "title" => 'Indexed dies, hydraulic force, low power draw',
                        "items" => [
                            'Three indexed die stations progressively reduce tube-end diameter in a single automated cycle',
                            'A hydraulic cylinder drives the point rod through each die stage with precise, repeatable force control',
                            'Load-sensing hydraulic pump delivers power only as needed, cutting energy consumption',
                            'Cable-carrier-managed hydraulic &amp; electrical lines keep the moving carriage tidy and easy to service',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Applications',
                "chips" => [
                    'Pre Cold Draw',
                    'Thick Wall Tube',
                    'SS Tubes',
                    'Alloy Tubes',
                    'Copper Tubes',
                    'Brass Tubes',
                ],
            ],
        ],
        "galleries" => [
        ],
        "highlight" => [
            "label" => 'The pre-process for cold drawing',
            "text" => 'The Push Pointing Machine is the essential pre-operation step before cold drawing, and is best paired with a Draw Bench.',
            "pairs" => [
                'draw-bench',
            ],
        ],
        "cta" => 'Ready to optimize your cold drawing process?',
    ],
    "straightening" => [
        "slug" => 'straightening',
        "tag" => 'Straightening Machine',
        "title" => [
            'Straightening',
            'Machine',
            '',
        ],
        "tagline" => 'Tube and bar straightening by bending & crushing modes. Precision tolerance of 0.5mm in 1000mm length — with optional 0.1mm precision achievable.',
        "hero" => 'product-straightening/hero',
        "heroCaption" => [
            'TST-Series Straightening Machine',
            'Shop floor installation with independently driven roll stands',
        ],
        "specLabel" => 'Key Specifications',
        "specs" => [
            [
                'Tube OD*',
                '16–300 mm',
            ],
            [
                'Wall Thickness*',
                '1.2–16 mm',
            ],
            [
                'Tube Length*',
                '5–12 m',
            ],
            [
                'Straightness',
                'Up to 0.15 mm/m',
            ],
            [
                'Speed',
                'Up to 40 m/min',
            ],
            [
                'Rolls',
                '6 / 10 / 14 / 16',
            ],
        ],
        "info" => [
            [
                'Materials Processed',
                [
                    'Mild Steel · Alloy & Stainless Steel',
                    'Aluminium Alloys · Copper & Brass Tubes',
                    'Solid Rod Stock',
                ],
            ],
        ],
        "callout" => null,
        "features" => [
            "title" => 'Key Features',
            "items" => [
                'Hyperbolic specially designed roll profile for precision straightening',
                'Main structure validated by Finite Element Analysis for minimum deflection',
                'Hydraulic clamping system to maintain roll position precisely throughout process',
                'Automatic configuration for setting linear and angular position',
                'Digital readout for roll position — reduces setting time significantly',
                'Both Horizontal (H) and Vertical (V) configurations available',
                'Inlet &amp; exit handling systems minimize noise and surface defects',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => 'Roll Drive & Power Transmission',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-straightening/mech-1',
                        "title" => 'Independently driven roll stations',
                        "items" => [
                            'Each roll stand is independently driven via cardan shafts from dedicated gearboxes for precise torque control',
                            'Heavy-duty gearbox &amp; shaft-drive train transmits high torque smoothly with minimal backlash',
                            'Modular drive layout allows easy access for maintenance and quick roll change-over',
                            'Multiple horizontal &amp; vertical roll stands work in sequence, progressively straightening the tube or bar',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Applications',
                "chips" => [
                    'Front Fork Tubes',
                    'Axle Tubes',
                    'Boiler Tubes',
                    'Heat Exchanger Tubes',
                    'Hydraulic Cylinder Tubes',
                    'Automotive Tubes',
                    'Seamless Tubes',
                    'ERW Tubes',
                    'Stainless Steel Tubes',
                    'Alloy Steel Tubes',
                    'Copper & Brass Tubes',
                    'Solid Rods & Shafts',
                ],
            ],
        ],
        "galleries" => [
            [
                "img" => 'product-straightening/gallery-1',
                "title" => 'In-Line Production Setup',
                "text" => 'TIMB straightening machines integrate with automated in-feed & out-feed roller conveyors for continuous, high-throughput operation.',
            ],
        ],
        "highlight" => [
            "label" => 'The downstream step for your tube line',
            "text" => 'A natural downstream process for any Tube Mill or Draw Bench line. TIMB has delivered 10 straightening machines, each uniquely designed with continuous improvements to meet specific customer needs.',
            "pairs" => [
                'tube-mill',
                'draw-bench',
            ],
        ],
        "cta" => 'Get precision straightening for your production line:',
    ],
    "rotary-swaging" => [
        "slug" => 'rotary-swaging',
        "tag" => 'Rotary Swaging Machine',
        "title" => [
            'Rotary',
            'Swaging',
            'Machine',
        ],
        "tagline" => 'Chipless metal forming using rapid hammer strikes. Size, reduce, shape, taper, bond or form metal parts with zero material waste.',
        "hero" => 'product-rotary-swaging/hero',
        "heroCaption" => [
            'TI-Series Rotary Swaging Machine',
            'Shop floor installation with integrated hydraulic power pack',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Tube Capacity',
                '5–110 mm',
            ],
            [
                'Solid Capacity',
                '2–40 mm',
            ],
            [
                'Types',
                '3 Variants',
            ],
            [
                'Models',
                'TI10 to TI100',
            ],
        ],
        "info" => [
            [
                'Materials Processed',
                [
                    'Mild Steel · Alloy & Stainless Steel',
                    'Titanium Alloys · Copper & Brass',
                    'Aluminium Alloys',
                ],
            ],
        ],
        "callout" => [
            'Model Range',
            'TI 10 · TI 25 · TI 45 · TI 65 · TI 80 · TI 100 — 6 variants, 5mm to 110mm tube capacity',
        ],
        "features" => null,
        "types" => [
            "title" => '3 Types of Swaging',
            "items" => [
                [
                    'Heavy Duty / Long Taper Swaging',
                    'For general engineering: tube pointing prior to cold drawing, furniture & bicycle frames',
                ],
                [
                    'Precision Swaging',
                    'For tubular & rod-type components with feed & die closing; spline forming capability',
                ],
                [
                    'Dual Rotary Swaging',
                    'Outer cage & inner spindle rotate in opposite directions; better surface finish for drive shaft & propeller shaft',
                ],
            ],
        ],
        "mech" => [
            [
                "title" => 'How Rotary Swaging Works',
                "sub" => '',
                "groups" => [
                    [
                        "num" => '',
                        "img" => 'product-rotary-swaging/mech-1',
                        "title" => 'Radial dies, rapid strikes, zero waste',
                        "items" => [
                            'A set of shaped dies arranged radially around the workpiece close and open at high frequency',
                            'Each strike incrementally reduces diameter or forms a taper — no material is cut or removed',
                            'The rod or tube is fed axially through the die aperture as forming progresses',
                            'Result: a cold-worked, dimensionally precise, chipless-formed component',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Applications',
                "chips" => [
                    'Steering Shafts',
                    'Drive Shafts',
                    'Camshafts',
                    'Axle Shafts',
                    'Propeller Shafts',
                    'EV Motor Shafts',
                    'Hydraulic Tubes',
                    'AC Tubes',
                    'Medical Needles',
                    'Endoscope Tubes',
                    'Anchor Bolts',
                    'Hollow Pistons',
                ],
            ],
        ],
        "galleries" => [
            [
                "img" => 'product-rotary-swaging/gallery-1',
                "title" => 'In-Line Production Setup',
                "text" => 'TIMB swaging machines integrate with automated feed & handling conveyors for continuous, high-throughput production runs.',
            ],
            [
                "img" => 'product-rotary-swaging/gallery-2',
                "title" => 'Complete Unit with Hydraulic Power Pack',
                "text" => 'Each swaging machine is supplied as a complete package — main swaging head, load-out conveyor, and dedicated hydraulic power packs — ready for shop floor installation.',
            ],
        ],
        "highlight" => [
            "label" => 'Key Benefits',
            "text" => '',
            "chips" => [
                'Better finish',
                'Speeds assembly',
                'Saves labour with auto-feeding',
                'Low-cost & flexible tooling',
                'Precise wall thickness control',
                'Chipless process = zero material waste',
            ],
        ],
        "cta" => 'Discuss your swaging requirement with our team:',
    ],
    "tube-mill" => [
        "slug" => 'tube-mill',
        "tag" => 'Tube Mill',
        "title" => [
            'Tube',
            'Mill',
            '',
        ],
        "tagline" => 'High-rigidity tube forming system for specialized tubing across automotive, structural, furniture, and energy sectors.',
        "hero" => 'product-tube-mill/hero',
        "heroCaption" => [
            'TM-Series Tube Mill',
            'Full continuous roll-forming line from coil to finished tube',
        ],
        "specLabel" => 'Key specifications',
        "specs" => [
            [
                'Diameter Range',
                '10–254 mm',
            ],
            [
                'Thickness',
                '1–16 mm',
            ],
            [
                'D/T Ratio',
                'Up to 7',
            ],
            [
                'Models',
                'TM41 to TM254',
            ],
        ],
        "info" => [
            [
                'Materials Processed',
                [
                    'Mild & Stainless Steel · Aluminium',
                    'Brass, Copper & Alloys',
                    'Ferrous & Non-Ferrous Coil Stock',
                ],
            ],
        ],
        "callout" => [
            'Model Range',
            'TM 41.28 · TM 76.20 · TM 101.60 · TM 168 · TM 254 — 5 variants, 12.7mm to 254mm diameter range',
        ],
        "features" => [
            "title" => 'Key Advantages',
            "items" => [
                'High productivity with reliable reproducibility and short set-up times',
                'Continuous production with automated processes and increased process stability',
                'Simple operation with IIoT features for Industry 4.0 readiness',
                'Modular design — individual elements configurable for specific production needs',
                'Handles Ferrous &amp; Non-Ferrous materials: SS, Aluminium, Brass, Copper &amp; Alloys',
                'Individual tube mill elements can be supplied to retrofit existing tube mills',
            ],
        ],
        "types" => null,
        "mech" => [
            [
                "title" => 'Coil to Tube: The Full Line',
                "sub" => '5 stages, one continuous process',
                "groups" => [
                    [
                        "num" => '1',
                        "img" => 'product-tube-mill/mech-1',
                        "title" => 'Coiler Storage, Uncoiler, Flattener & Shear Welder',
                        "items" => [
                            'Coil storage yard holds incoming raw material coils, ready for uncoiling',
                            'Motorized uncoiler pays off strip under controlled tension while the flattener levels coil-set and camber',
                            'Shear &amp; welder butt-splices the tail of one coil to the head of the next for uninterrupted, continuous running',
                            'Weld bead is trimmed and the splice annealed for a smooth, defect-free joint before it moves downstream',
                        ],
                    ],
                    [
                        "num" => '2',
                        "img" => 'product-tube-mill/mech-2',
                        "title" => 'Strip Accumulator & Pinch Roller',
                        "items" => [
                            'Strip accumulator stores several minutes of material, letting the mill run non-stop through coil changeovers',
                            'Motorized rotating carriage pays out strip smoothly without interrupting line speed',
                            'Pinch rollers grip and drive the strip forward into the forming section at controlled tension',
                            'Eliminates downtime for splicing &amp; coil loading — critical for maximizing OEE',
                        ],
                    ],
                    [
                        "num" => '3',
                        "img" => 'product-tube-mill/mech-3',
                        "title" => 'Roll Forming Stand, Fin Pass Stand & Squeeze Roll',
                        "items" => [
                            'Roll forming stands progressively curl the flat strip into a round tube profile',
                            'Fin pass stands fine-tune the round profile just before the weld box',
                            'Squeeze rolls apply precise pressure at the High-Frequency (HF) induction weld point for a clean, consistent seam',
                            'Modular stand design allows elements to be added or reconfigured for new product ranges',
                        ],
                    ],
                    [
                        "num" => '4',
                        "img" => 'product-tube-mill/mech-4',
                        "title" => 'Cooling Chamber, Sizing & Turk Head',
                        "items" => [
                            'Cooling chamber rapidly cools the freshly HF-welded tube seam',
                            'Sizing module brings the tube down to precise finished OD and roundness',
                            'Turk head fine-sizes the tube on all sides for tight dimensional tolerance',
                            'Ensures straightness and a consistent wall profile before cut-off',
                        ],
                    ],
                    [
                        "num" => '5',
                        "img" => 'product-tube-mill/mech-5',
                        "title" => 'Cut-Off',
                        "items" => [
                            'Synchronized flying cut-off cleanly parts the continuous tube to length without stopping the line',
                            'Cut lengths transfer automatically onto the exit conveyor for collection',
                            'Cut ends are deburred for safe handling and packing',
                            'Integrated with line PLC for automatic length control &amp; production counting',
                        ],
                    ],
                ],
            ],
        ],
        "apps" => [
            [
                "title" => 'Applications',
                "chips" => [
                    'Automotive Tubes',
                    'Front Fork Tubes',
                    'Axle Tubes',
                    'Shock Absorber Tubes',
                    'Exhaust Tubes',
                    'Heat Exchanger Tubes',
                    'Boiler Tubes',
                    'Hydraulic Tubes',
                    'Refrigeration & AC Tubes',
                    'Precision Mechanical Tubes',
                    'Oil & Gas Tubes',
                ],
            ],
        ],
        "galleries" => [
            [
                "img" => 'product-tube-mill/gallery-1',
                "title" => 'Complete Line Engineering',
                "text" => 'Every TM-Series tube mill is engineered end-to-end — twin-motor drive gearbox, precision roll-forming stands and exit sizing/cut-off — as one continuous, synchronized production line.',
            ],
        ],
        "highlight" => [
            "label" => 'Why TIMB Tube Mill?',
            "text" => '',
            "chips" => [
                'High Productivity',
                'Precision Tubes',
                'Reliable Operation',
                'Advanced Automation',
                'Customised Solutions',
                'Complete Project Support',
            ],
        ],
        "cta" => 'Ready to discuss your tube production requirement?',
    ],
];

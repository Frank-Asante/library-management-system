		
    	<style>
        .grid-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .grid-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .grid-item img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            aspect-ratio: 4/3;
        }

        .grid-item:hover {
            transform: scale(1.05);
        }

        @media (max-width: 600px) {
            .image-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="grid-container">
        <div class="image-grid">
            <div class="grid-item">
                <img src="LMS/1.jpg" alt="Image 1">
            </div>
            <div class="grid-item">
                <img src="LMS/2.jpg" alt="Image 2">
            </div>
            <div class="grid-item">
                <img src="LMS/3.jpg" alt="Image 3">
            </div>
            <div class="grid-item">
                <img src="LMS/5.jpg" alt="Image 5">
            </div>
            <div class="grid-item">
                <img src="LMS/4.jpg" alt="Image 4">
            </div>
            <div class="grid-item">
                <img src="LMS/6.jpg" alt="Image 6">
            </div>
            <div class="grid-item">
                <img src="LMS/7.jpg" alt="Image 7">
            </div>
            <div class="grid-item">
                <img src="LMS/8.jpg" alt="Image 8">
            </div>
            <div class="grid-item">
                <img src="LMS/9.jpg" alt="Image 9">
            </div>
            <div class="grid-item">
                <img src="LMS/10.jpg" alt="Image 10">
            </div>
            <div class="grid-item">
                <img src="LMS/11.jpg" alt="Image 13">
            </div>
            <div class="grid-item">
                <img src="LMS/12.jpg" alt="Image 14">
            </div>
        </div>
    </div>
    
    
    </section> 
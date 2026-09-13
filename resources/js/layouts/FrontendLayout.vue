<script setup>
import { CContainer } from '@coreui/vue'
import Header from '@/components/frontend/Header.vue'
import Footer from '@/components/frontend/Footer.vue'
</script>

<template>
<!-- ==loader Two== -->
  <!-- <div class="loaderWrap">
    <div class="loader">
      <img src="images/loder.gif">
    </div>
  </div> -->
  <!-- Back To Top -->
  <div id="backto-top" class="go-top go-top-button">
    <i class="fa"><img src="images/top.png" alt=""></i>
    <i class="fa"><img src="images/top.png" alt=""></i>
    <!-- <img src="images/top.png" alt=""> -->
  </div>

  <header>

	<?php include './include/header.php'; ?>

	<div class="banner_main ">

		<div class="w-100 bannerimg">
			<img src="images/banner-img.png" alt="banner images" class="w-100">
		</div>

		<div class="carousel-caption text-start">
			<div class="container">
			<div class="row align-items-center  g-4">
				<div class="col-md-8">
					<h4><span>Trusted Home Healthcare in Kolkata</span></h4>
				<h1>Verified Caregivers <br>Across Kolkata -<br><span>Available within 24 Hours.</span></h1>
				<p>Professional care. Personal touch. Peace of mind for your family. <br> Elder Care, post-surgery support, and nursing care - all at home.</p>

				<div class="trustus">
					<div class="row g-3">
						<div class="col-sm-6 col-xl-3">
							<div class="d-flex align-items-start">
								<figure>
									<img src="images/excellence.png" alt="img">
								</figure>
								<figcaption>
									<h3>100% Verified</h3>
									<p>Background & Police verified</p>
								</figcaption>
							</div>
						</div>
						<div class="col-sm-6 col-xl-3">
							<div class="d-flex align-items-start">
								<figure>
									<img src="images/accountability.png" alt="img">
								</figure>
								<figcaption>
									<h3>Trained/Experienced</h3>
									<p>Regularly Traiend for Quality Care</p>
								</figcaption>
							</div>
						</div>
						<div class="col-sm-6 col-xl-3">
							<div class="d-flex align-items-start">
								<figure>
									<img src="images/care.png" alt="img">
								</figure>
								<figcaption>
									<h3>Same-day</h3>
									<p>We Respond fast, So you don't worry</p>
								</figcaption>
							</div>
						</div>
						<div class="col-sm-6 col-xl-3">
							<div class="d-flex align-items-start">
								<figure>
									<img src="images/support-available.png" alt="img">
								</figure>
								<figcaption>
									<h3>24/7 Support</h3>
									<p>We're always here when you need us</p>
								</figcaption>
							</div>
						</div>
					</div>
				</div>

				<div class="text-start mt-3">
					<a href="https://wa.me/918697000128?text=Hi, I'm coming from your Website! I want to know more about your healthcare services." class="btn btnstyle me-2 whatsappbtn"><span><i class="fa fa-whatsapp"></i> WhatsApp Us</span></a>

					<a href="#0" class="btn btnstyle" data-bs-toggle="modal" data-bs-target="#profileModal"><span>Need CareGiver</span></a>
				</div>

				</div>
			</div>
			</div>
		</div>

	</div>

</header>
 <?php if(!empty($_SESSION['flash_msg'])){ ?>
	<div class="alert alert-success">
		<?php echo $_SESSION['flash_msg'];unset( $_SESSION['flash_msg']);?>
	</div>
	<?php } ?>
<section class="ourhomeservice sectiongap">
	<div class="container">
		<div class="sectiontitle text-center">
			<h3><span>Our Home Healthcare Services</span></h3>
		</div>

		<div class="row g-4">
			<div class="col-md-6 col-xl-3">
				<a href="caregiver-at-home.php" class="serbox">
					<figcaption>
						<div class="icon">
							<img src="images/caregiver.png" alt="reliability">
						</div>
						<h4>Caregiver at Home</h4>
						<p>Services to ensure comfort, safety, and peace of mind</p>
					</figcaption>
					<figure>
						<img src="images/caregiver_at_home.png" alt="img3" class="w-100">
					</figure>
				</a>
			</div>
			<div class="col-md-6 col-xl-3">
				<a href="#0" class="serbox">
					<figcaption>
						<div class="icon">
							<img src="images/care.png" alt="reliability">
						</div>
						<h4>Elder Care Services</h4>
						<p>Compassionate care for seniors with daily living assistance</p>
					</figcaption>
					<figure>
						<img src="images/imgg2.png" alt="img3" class="w-100">
					</figure>
				</a>
			</div>
			<div class="col-md-6 col-xl-3">
				<a href="#0" class="serbox">
					<figcaption>
						<div class="icon">
							<img src="images/nurse.png" alt="reliability">
						</div>
						<h4>Nursing Care at Home</h4>
						<p>Professional nursing care by trained caregivers and nurses</p>
					</figcaption>
					<figure>
						<img src="images/Nursing_Care_at_home.png" alt="img3" class="w-100">
					</figure>
				</a>
			</div>
			<div class="col-md-6 col-xl-3">
				<a href="#0" class="serbox">
					<figcaption>
						<div class="icon">
							<img src="images/nurses.png" alt="reliability">
						</div>
						<h4>Physiotherapy at Home</h4>
						<p>Expert physiotherapy sessions in the comfort of home</p>
					</figcaption>
					<figure>
						<img src="images/Physiotherapy_at_home.png" alt="img3" class="w-100">
					</figure>
				</a>
			</div>
		</div>
		<!--<div class="text-start mt-4 text-center">
			<a href="#0" class="btn btnstyle"><span>View All Services</span></a>
		</div>-->
	</div>
</section>

<section class="ourhomeservice sectiongap pt-0">
	<div class="container">
		<div class="sectiontitle text-center">
			<h3><span>Our Verified Caregivers</span></h3>
		</div>

		<div class="row g-4">
			<div class="col-md-6 col-xl-4">
				<div class="gnmbox ">
					<figure>
						<img src="images/Caregiver_Profile_Photo_1.png" alt="caregivers1">
					</figure>
					<figcaption>
						<h3>Puja Mondal</h3>
						<h5>General Duty Assistant (GDA)</h5>
						<p><b><i class="fa fa-briefcase" aria-hidden="true"></i> Experience:</b> 4 Years</p>
						<p><b><i class="fa fa-book" aria-hidden="true"></i> Qualification:</b> 12th Pass, GDA Trained</p>
						<p><b><i class="fa fa-user" aria-hidden="true"></i> Specialization:</b> Elder Care, Patient Assistance, Daily Living Support</p>
						<a href="#0" class="btn btn-varifid"><i class="fa fa-check-square-o"></i>Police Verified</a>
					</figcaption>
				</div>
			</div>
			<div class="col-md-6 col-xl-4">
				<div class="gnmbox ">
					<figure>
						<img src="images/Caregiver_Profile_Photo_2.png" alt="caregivers1">
					</figure>
					<figcaption>
						<h3>Mousumi Das</h3>
						<h5>Staff Nurse (GNM)</h5>
						<p><b><i class="fa fa-briefcase" aria-hidden="true"></i> Experience:</b> 6 Years</p>
						<p><b><i class="fa fa-book" aria-hidden="true"></i> Qualification:</b> General Nursing and Midwifery</p>
						<p><b><i class="fa fa-user" aria-hidden="true"></i> Specialization:</b> Nursing Care, Post-Surgery Care, Medication Management</p>
						<a href="#0" class="btn btn-varifid"><i class="fa fa-check-square-o"></i>Police Verified</a>
					</figcaption>
				</div>
			</div>
			<div class="col-md-6 col-xl-4">
				<div class="gnmbox ">
					<figure>
						<img src="images/Caregiver_Profile_Photo_3.png" alt="caregivers1">
					</figure>
					<figcaption>
						<h3>Rina Sharma</h3>
						<h5>Caregiver & Attendant</h5>
						<p><b><i class="fa fa-briefcase" aria-hidden="true"></i> Experience:</b> 3 Years</p>
						<p><b><i class="fa fa-book" aria-hidden="true"></i> Qualification:</b> 12th Pass, Caregiver Trained</p>
						<p><b><i class="fa fa-user" aria-hidden="true"></i> Specialization:</b> Bedridden Care, Elder Care, Hygiene Assistance</p>

						<a href="#0" class="btn btn-varifid"><i class="fa fa-check-square-o"></i>Police Verified</a>

					</figcaption>
				</div>
			</div>
		</div>


	</div>
</section>


 <!-- <section class="dreem">
	<div class="container">
		<div class="drmaindiv">
			<div class="row justify-content-center g-4">
				<div class="col-sm-6 col-md-3 border-end border_light">
					<div class="drbox d-flex align-items-center justify-content-center">
						<figure>
							<img src="images/happyfamile.png" alt="img">
						</figure>
						<figcaption>
							<h3><span>Happy Families  </span> 1000+</h3>
						</figcaption>
					</div>
				</div>
				<div class="col-sm-6 col-md-3 border-end border_light">
					<div class="drbox d-flex align-items-center justify-content-center">
						<figure>
							<img src="images/varified-caregivers.png" alt="img">
						</figure>
						<figcaption>
							<h3><span>Verified Caregivers</span> 500+</h3>
						</figcaption>
					</div>
				</div>
				<div class="col-sm-6 col-md-3 border-end border_light">
					<div class="drbox d-flex align-items-center justify-content-center">
						<figure>
							<img src="images/support-available.png" alt="img">
						</figure>
						<figcaption>
							<h3><span>Support Available</span> 24x7</h3>
						</figcaption>
					</div>
				</div>
				<div class="col-sm-6 col-md-3">
					<div class="drbox d-flex align-items-center justify-content-center">
						<figure>
							<img src="images/kolkata.png" alt="img">
						</figure>
						<figcaption>
							<h3><span>Serving Kolkata</span> 5+ Years</h3>
						</figcaption>
					</div>
				</div>
			</div>
		</div>
	</div>
  </section> -->

<section class="sectiongap pt-0">
	<div class="container">
		<div class="sectiontitle text-center">
			<h3><span>How It Works</span></h3>
		</div>

		<div class="row g-4">
			<div class="col-sm-6 col-xl-3">
				<div class="workbox">
					<b>1</b>
					<div class="numbox"><img src="images/contacts.png" alt="contacts"></div>
					<h4>Contact Us</h4>
					<p>Share your requirement with our care team.</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-3">
				<div class="workbox">
					<b>2</b>
					<div class="numbox"><img src="images/assessment.png" alt="assessment"></div>
					<h4>Assessment</h4>
					<p>We understand patient needs and preferences.</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-3">
				<div class="workbox">
					<b>3</b>
					<div class="numbox"><img src="images/caregiver.png" alt="caregiver"></div>
					<h4>Caregiver Match</h4>
					<p>We assign a verified caregiver for you.</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-3">
				<div class="workbox be-0">
					<b>4</b>
					<div class="numbox"><img src="images/start-up.png" alt="start-up"></div>
					<h4>Care Starts</h4>
					<p>Care begins at your convenience.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="sectiongap company-profile pt-0">
	<div class="container">

		<div class="sectiontitle text-center">
			<h3><span>Beyond Home Care</span></h3>
		</div>

		<div class="row g-4">
			<div class="col-md-6">
				<div class="service-card text-start m-0 ">
					<h4>Medical Consumables</h4>
					<p>We provide a wide range of medical consumables at your doorstep.</p>
					<img src="images/Medical_Consumables.png" alt="">
					<a class="btnstyle " href="#0"><span>View Catalog</span></a>
				</div>
			</div>
			<div class="col-md-6">
				<div class="service-card text-start m-0  bgimages">
					<h4>Training & Careers</h4>
					<p>Build your career in healthcare with our certified training program.</p>
					<ul>
						<li>Patient Care Assistant (1 Year)</li>
						<li>100% Practical Training</li>
						<li>Placement Assistance</li>
					</ul>
					<a class="btnstyle " href="#0"><span>Apply for Training</span></a>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="sectiongap pt-0">

	<div class="container">

		<div class="sectiontitle text-center">
			<h3><span>Frequently Asked Questions</span></h3>
		</div>

		<div class="accordion" id="accordionExample">

			<!-- Item 2 -->
			<div class="accordion-item">
			<h2 class="accordion-header" id="headingTwo">
				<button class="accordion-button collapsed custom-accordion-btn" type="button" data-bs-toggle="collapse"
				data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
				<div class="container d-flex justify-content-between align-items-center">
					<span>1. What home healthcare services do you provide?</span>
					<div class="accordion-arrow"></div>
				</div>
				</button>
			</h2>
			<div id="collapseTwo" class="accordion-collapse collapse"
				aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
				<div class="accordion-body">
				<div class="container">
					<p>We offer elder care, nursing care, caregiver services, physiotherapy, doctor visits, post-surgery care, ICU care at home, medical equipment, and trained attendants.</p>
				</div>
				</div>
			</div>
			</div>
			<!-- Item 3 -->
			<div class="accordion-item">
			<h2 class="accordion-header" id="headingThree">
				<button class="accordion-button collapsed custom-accordion-btn" type="button" data-bs-toggle="collapse"
				data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
				<div class="container d-flex justify-content-between align-items-center">
					<span>2. Are your caregivers verified?</span>
					<div class="accordion-arrow"></div>
				</div>
				</button>
			</h2>
			<div id="collapseThree" class="accordion-collapse collapse"
				aria-labelledby="headingThree" data-bs-parent="#accordionExample">
				<div class="accordion-body px-0">
				<div class="container">
					<p>Yes. All our caregivers undergo identity verification, background checks, police verification, and professional training.</p>
				</div>
				</div>
			</div>
			</div>
			<!-- Item 4 -->
			<div class="accordion-item">
			<h2 class="accordion-header" id="heading4">
				<button class="accordion-button collapsed custom-accordion-btn" type="button" data-bs-toggle="collapse"
				data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
				<div class="container d-flex justify-content-between align-items-center">
					<span>3. Which areas do you serve?</span>
					<div class="accordion-arrow"></div>
				</div>
				</button>
			</h2>
			<div id="collapse4" class="accordion-collapse collapse"
				aria-labelledby="heading4" data-bs-parent="#accordionExample">
				<div class="accordion-body px-0">
				<div class="container">
					<p>We provide home healthcare services across Kolkata and nearby areas.</p>
				</div>
				</div>
			</div>
			</div>

		</div>

	</div>

</section>

<section class="sectiongap pt-0">
	<div class="container">
		<div class="sectiontitle text-center">
			<h3><span>Our Associates</span></h3>
		</div>

		<div class="owl-carousel patners">
  <div class="patnerslogo"><img src="images/Logo-1.png" alt=""></div>

  <div class="patnerslogo"><img src="images/Logo-2.png" alt=""></div>

  <div class="patnerslogo"><img src="images/Logo_3.jpg" alt=""></div>

  <div class="patnerslogo"><img src="images/Logo-4.png" alt=""></div>

  <div class="patnerslogo"><img src="images/Logo-5.png" alt=""></div>

  <div class="patnerslogo"><img src="images/Logo-6.png" alt=""></div>
</div>

	</div>
</section>
</template>

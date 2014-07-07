<?php
	header('Content-Type: text/css');
	include_once(_PS_MODULE_DIR_.'homesliderpro/homesliderpro.php');
	$slider = new HomeSliderPro;
?>
.sliderFigo ul {
	list-style:none;
}

.sliderFigo a, .sliderFigo a img {
	text-decoration:none;
	outline:none;
}

.sliderFigo:after {
  clear: both;
  content: ".";
  display: block;
  font-size: 0;
  height: 0;
  overflow: hidden;
}
 
.noJs .sliderFigo li {
	display:none;
}

.noJs .sliderFigo li.primo{
	display:block;
}

.bx-wrapper {
	margin-bottom:10px;
	position:relative;
}

.sliderFigo .bx-wrapper {
  clear: both;
  margin-bottom: 10px;
}

.bx-wrapper a, .bx-wrapper a:hover {
	text-decoration:none;
}
 
/*next prev button*/

.bx-next, .bx-prev {
	background:#000;
  background: none repeat scroll 0 0 rgba(0, 0, 0, 0.5);
  border-radius: 30px;
  display: block;
  height: 32px;
  line-height: 0;
  margin-top: -15px;
  opacity: 0.4;
  overflow: hidden;
  position: absolute;
  text-indent: -999em;
  top: 50%;
  transition: all 0.3s ease 0s;
  -webkit-transition: all 0.3s ease 0s;
  width: 32px;
  z-index: 100;
}

.bx-next:hover, .bx-prev:hover{
	text-decoration:none;
}

.bx-prev:after, .bx-next:after {
  color: #FFFFFF;
  content: "<";
  display: table-cell;
  font-family: monospace;
  font-size: 30px;
  font-weight: bold;
  height: 100%;
  left: 0;
  line-height: 117%;
  position: absolute;
  text-align: center;
  text-decoration: none;
  text-indent: 0;
  top: 0;
  vertical-align: middle;
  width: 100%;
  z-index: 150;
}

.bx-next:after {
	content: ">";
}

.shop1_2 .bx-next, .shop1_2 .bx-prev {
	background: url("leftright-shop1_2.png") no-repeat scroll 0 0 transparent;
}

.bx-next, .shop1_2 .bx-next {
  background-position:0 -32px;
  right: 10px;
}

.bx-prev {
  left: 5px;
}
/*next/prev button hover state*/
.bx-next:hover,
.bx-prev:hover {
	opacity:1.0;
	filter:alpha(opacity=100); /* For IE8 and earlier */
}

/*pager links*/
.bx-controls {
  color: #666666;
  font-size: 11px;
  left: 0;
  text-align: left;
}

.bx-pager {
  bottom: 15%;
  left: 0;
  padding: 0 0 0 5%;
  position: absolute;
  z-index: 100;
}

.pager-centered .bx-pager {
  bottom: 15%;
  left: 0;
  padding: 0 0 0 5%;
  position: absolute;
  z-index: 100;
}

.bx-pager-item {
  border: 3px solid #aaa;
  border: 3px solid rgba(0,0,0,0.5);
  border-radius: 30px;
  float: left;
  height: 20px;
  margin: 0 3px;
  width: 20px;
  transition: all 0.15s;
  -webkit-transition: all 0.15s ease 0s;
  cursor:pointer;
  display:inline-block;
  position:relative;
}

.bx-pager-item:hover .bx-pager-link {
	background-color:#FFA500;
	height: 16px;
	width: 16px;
	margin: 2px;
}

.bx-pager-item:hover {
	box-shadow:0 0 3px #fff;
	border:3px solid #fff;
}

.bx-pager a {
  background: none repeat scroll 0 0 #0090F0;
  border-radius: 30px;
  color: #000000;
  display: inline-block;
  font-size: 11px;
  font-weight: bold;
  height: 10px;
  margin: 5px;
  padding: 0;
  text-decoration: none;
  text-indent: -999em;
  transition: all 0.15s ease 0s;
  -webkit-transition: all 0.15s ease 0s;
  width: 10px;
  position:absolute;
  left:0;
  top:0;
}

.shop1_2 .bx-pager a {
	background:url(imgs/pallino-shop1_2.png) no-repeat 0 0;
}

.bx-controls-direction {}

/*
 * End color scheme styles
 */




/*pager links hover and active states*/
.bx-pager-link.active {
	background-color:#FFA500;
	height: 16px;
	width: 16px;
	margin: 2px;
}

/*captions*/
.bx-captions {
	text-align:center;
	font-size: 12px;
	padding: 7px 0;
	color: #666;
}

/*auto controls*/
.bx-auto {
	text-align: center;
	padding-top: 15px;
}

.bx-auto a {
	color: #666;
	font-size: 12px;
}

.sliderFigo li {
	position:relative;
}

.sliderFigo li img {
	width:100%;
	height:auto;
}

.slidetitle {
	position:absolute;
	top:5%;
	padding:5px 10px;
	color:#fff;
	background:rgba(0,0,0,0.5);
	font-weight:normal;
}

.shop1_2 .slidetitle {
	background:url(imgs/blue-trans.png);
}

.slidetitle.right {
	right:0;
}

.slidetitle.left {
	left:0;
}

.sliderFigo:hover .slide_description{
	height:auto;
	padding: 10px 2%;
}

.slide_description {
  background: none repeat scroll 0 0 rgba(0, 0, 0, 0.5);
  bottom: 0;
  color: #FFFFFF;
  display: block;
  height: 0;
  overflow: hidden;
  padding: 0 2%;
  position: absolute;
  transition: all 0.3s ease 0s;
  -webkit-transition: all 0.3s ease 0s;
  width: 96%;
}

.slider_title_block.bgcolor {
    color: #FFFFFF;
    font-weight: bold;
    margin: 10px 0;
    padding: 4px 11px 5px;
    text-align: left;
    text-transform: capitalize;
	font-size:14px;
}

/* altri sliders*/

.sliderFigo.bxslider_1 {
  left: 190px;
  position: absolute;
  top: 23px;
  width: 370px;
  height: 93px;
}

.sliderFigo {
	margin-bottom:10px;
}

.sliderFigo.bxslider_2, .sliderFigo.bxslider_6 {
  float: left;
  width: 40%;
}

.sliderFigo.bxslider_2, .sliderFigo.bxslider_5 {
	margin: 0 1% 10px 0;
}

.sliderFigo.bxslider_3, .sliderFigo.bxslider_5 {
  width: 59%;
  float:left;
}

.sliderFigo.bxslider_4 {
  clear: both;
}

.sliderFigo li img {
  display: block;
  line-height: 120%;
  vertical-align: bottom;
}

/* 3d Flip Mode*/

.sliderFigo.mode_3Dflip li {
	-webkit-transform:perspective(2000px) rotateY(-90deg);
	transform:perspective(2000px) rotateY(-90deg);
	-webkit-transition:all 300ms ease-in 0s;
	transition:all 0 ease-in 0s;
}

.sliderFigo.mode_3Dflip li.left {
	-webkit-transform:perspective(2000px) rotateY(90deg);
	transform:perspective(2000px) rotateY(90deg);
	-webkit-transition:all 300ms ease-in 0s;
	transition:all 0 ease-in 0s;
}

.sliderFigo.mode_3Dflip li.old.next {
	-webkit-transform:perspective(2000px) rotateY(90deg);
	transform:perspective(2000px) rotateY(90deg);
	-webkit-transition:all 300ms ease-in 0s;
	transition:all 300ms ease-in 0s;
}

.sliderFigo.mode_3Dflip li.active-slide {
	-webkit-transform:perspective(2000px) rotateY(0deg);
	transform:perspective(2000px) rotateY(0deg);
	-webkit-transition:all 300ms ease-out 300ms;
	transition:all 300ms ease-out 300ms;
}

.sliderFigo.mode_3Dflip li.old.prev {
	-webkit-transform:perspective(2000px) rotateY(-90deg);
	transform:perspective(2000px) rotateY(-90deg);
	-webkit-transition:all 300ms ease-in 0s;
	transition:all 300ms ease-in 0s;
}
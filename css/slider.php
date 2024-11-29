@import bourbon

$poussin: url("http://images.metmuseum.org/CRDImages/ep/original/46_160.jpg")
$picasso: url("http://uploads6.wikiart.org/images/pablo-picasso/the-abduction-of-sabines-1962-1.jpg")
$rubens:
url("https://upload.wikimedia.org/wikipedia/commons/7/72/Peter_Paul_Rubens_(taller)_-_Rapto_de_las_Sabinas.jpg")

html, body
margin: 0
height: 100%
background: #F4F1E9

.arrow
font-size: 2em
color: #363f85
position: absolute
top: 50%
left: 50%
&#left-arrow
+transform(translate(-11em, -50%))
&#right-arrow
+transform(translate(10em, -50%))
+transition(.2s)
&:hover
color: #B3B2AD

.slider
position: relative
left: 50%
top: 50%
+transform(translate(-50%, -50%))
width: 2000px
height: 200px

.slide
float: left
position: relative
width: percentage(1/3)
height: 100%
overflow-x: hidden
border-radius: 0%
box-shadow: 0px 0px 15px rgba(0,0,0,0.5)
&#slide-center
z-index: 1
box-shadow: 0px 0px 15px rgba(0,0,0,0.75)
+transform(scale(1.3))

.slide-holder
width: 300%
height: 100%
display: block
position: relative
top: 0
+transform(translateX(percentage(-1/3)))

.slide-bg
width: percentage(1/3)
height: 100%
background-size: cover
background-position: center center
background-repeat: no-repeat
display: inline-block
float: left
margin: 0

#slide-left
.bg-previous
background-image: $rubens
.bg-current
background-image: $poussin
.bg-next
background-image: $picasso
#slide-center
.bg-previous
background-image: $poussin
.bg-current
background-image: $picasso
.bg-next
background-image: $rubens
#slide-right
.bg-previous
background-image: $picasso
.bg-current
background-image: $rubens
.bg-next
background-image: $poussin
<!-- css -->
<!-- js -->
# Inspiration:
# http://coolcarousels.frebsite.nl/c/59/
# &
# https://css-tricks.com/slider-with-sliding-backgrounds/

$right_arrow = $('#right-arrow')
$left_arrow = $('#left-arrow')

$right_arrow.click (e) ->
e.preventDefault()
$('.slide-holder').velocity('finish') # finish any current animations
animate_next('#slide-right')
animate_next('#slide-center', 175)
animate_next('#slide-left', 350)

$left_arrow.click (e) ->
e.preventDefault()
$('.slide-holder').velocity('finish') # finish any current animations
animate_previous('#slide-left')
animate_previous('#slide-center', 175)
animate_previous('#slide-right', 350)

animate_next = (selector, delay=0, cb=null) ->
setTimeout ->
$el = $("#{selector} .slide-holder") # select the elements
$bg_prev = $el.find('.bg-previous')
$bg_curr = $el.find('.bg-current')
$bg_next = $el.find('.bg-next')
$.Velocity.hook($el, "translateX", "-#{100/3}%") # set transform before animating
$.Velocity.animate($el, { # animate the transform
translateX: "-#{200/3}%"
duration: 350
}).then (elms) -> # reorder the slide-bg's and recenter the slide-holder
next_bg_image = $.Velocity.hook($bg_prev, "background-image")
$.Velocity.hook($bg_prev, "background-image", $.Velocity.hook($bg_curr, "background-image"))
$.Velocity.hook($bg_curr, "background-image", $.Velocity.hook($bg_next, "background-image"))
$.Velocity.hook($el, "translateX", "-#{100/3}%")
$.Velocity.hook($bg_next, "background-image", next_bg_image)
cb(elms) if typeof cb is 'function'
, delay

animate_previous = (selector, delay, cb) ->
setTimeout ->
$el = $("#{selector} .slide-holder") # select the elements
$bg_prev = $el.find('.bg-previous')
$bg_curr = $el.find('.bg-current')
$bg_next = $el.find('.bg-next')
$.Velocity.hook($el, "translateX", "-#{100/3}%") # set transform before animating
$.Velocity.animate($el, { # animate the transform
translateX: "0"
duration: 350
}).then (elms) -> # reorder the slide-bg's and recenter the slide-holder
prev_bg_image = $.Velocity.hook($bg_next, "background-image")
$.Velocity.hook($bg_next, "background-image", $.Velocity.hook($bg_curr, "background-image"))
$.Velocity.hook($bg_curr, "background-image", $.Velocity.hook($bg_prev, "background-image"))
$.Velocity.hook($el, "translateX", "-#{100/3}%")
$.Velocity.hook($bg_prev, "background-image", prev_bg_image)
cb(elms) if typeof cb is 'function'
, delay
<!-- js -->
<!-- code contoh -->
#slider.slider
.slide#slide-left
.slide-holder
.slide-bg.bg-previous
.slide-bg.bg-current
.slide-bg.bg-next
.slide#slide-center
.slide-holder
.slide-bg.bg-previous
.slide-bg.bg-current
.slide-bg.bg-next
.slide#slide-right
.slide-holder
.slide-bg.bg-previous
.slide-bg.bg-current
.slide-bg.bg-next
a(href="#").arrow#left-arrow
i.fa.fa-arrow-circle-o-left
a(href="#").arrow#right-arrow
i.fa.fa-arrow-circle-o-right
<!DOCTYPE html>
<html>
<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<title	style="-moz-user-select: none;
	-webkit-user-select: none;
	-ms-user-select: none;
	user-select: none;
	pointer-events: none;">Crystal Field Theory Viewer</title>
		<link type="text/css" rel="stylesheet" href="style.css">
		<style>
			.label {
				color: var(--text-color);
				font-family: sans-serif;
				padding: 2px;
				background: rgba( 0, 0, 0, .0 );
			}
		</style>
	</head>
<body class="stop-scrolling">
<script async src="https://unpkg.com/es-module-shims@1.3.6/dist/es-module-shims.js"></script>

<script type="importmap">
  {
    "imports": {
      "three": "https://unpkg.com/three@0.148.0/build/three.module.js",
      "three/addons/": "https://unpkg.com/three@0.148.0/examples/jsm/"
    }
  }
</script>

<div class="info" style="	position: absolute;
	top: 0px;
	width: 100%;
	text-align: center;
	z-index: 0;
	display:block;">
	<h1>Crystal Field Theory Viewer</h1>
	<div class="info" style="margin: auto; position: relative; text-align: center; margin: auto; padding-right: 12em; padding-top: 2em; z-index: 0;">
	<h2>
		Energy---
	</h2>
	</div>
		<h2 style="text-align: center; top: -2em; right: -8em; position: relative; display:inline-block;">
		_____<br>
		 d<sub>xz</sub>
		</h2>
		<h2 style="text-align: center; top: -2em; right: -8em; position: relative; display:inline-block;">
		_____<br>
		d<sub>yz</sub>
		</h2>
		<h2 style="texti-align: center; top: -2em; right: -8em; position: relative; display:inline-block;">
		_____<br>
		d<sub>xy</sub>
		</h2>
		<h2 style="text-align: center; top: -6em; right: 0.5em; position: relative; display:inline-block;">
		_____<br>
		d<sub>x<sup>2</sup>-y<sup>2</sup></sub>
		</h2>
		<h2 style="text-align: center; top:-6em; right: 0.0em; position: relative; display:inline-block;">
		_____<br>
		d<sub>z<sup>2</sup></sub>
		</h2>
		<!--<h2 style="text-align: center; top:-5.12em; right: 4em; position: relative; display:inline-block;">
		---
		</h2>
		-->
		<h2 style="text-align: center; top:-6.4em; right: -1.1em; position: relative; display:inline-block; transform: scale(1.2, 2.5); transform-origin: left;">
			}
			<h2 style="text-align: center; top:-6.4em; right: -1.2em; position: relative; display:inline-block;">	0.4Δ
			</h2>
		</h2>
		<h2 style="text-align: center; top:-4.2em; right: 2.15em; position: relative; display:inline-block; transform: scale(1.2, 2.0); transform-origin: left;">
			}
			<h2 style="text-align: center; top:-4.2em; right: 2.05em; position: relative; display:inline-block;">	0.6Δ
			</h2>
		</h2>
</div>


<!--
<div id="iframe-goes-in-here">
  <div id="gui-container-left"></div>
  <iframe></iframe>
</div>
<div id="iframe-goes-in-here">
  <div id="gui-container-right"></div>
  <iframe></iframe>
</div>
-->
<div class="row-text">
  <div class="column" style=  "align-items: center;
  justify-content: center; display: flex; position: relative;">Geometry</div>
  <div class="column" style=  "align-items: center;
  justify-content: center; text-align: center; display: flex; position: relative;">Distances</div>
  <div class="column" style=  "align-items: center;
  justify-content: center; display: flex; position: relative;">Orbital</div>
</div>
<div class="row">
  <div class="column" id="gui-container-left"></div>
  <div class="column" id="gui-container-middle"></div>
  <div class="column" id="gui-container-right"></div>
</div>

<script type="module">

import * as THREE from 'three';
import  {OrbitControls} from 'three/addons/controls/OrbitControls.js';
import {GLTFLoader} from 'three/addons/loaders/GLTFLoader.js';
import {CSS2DRenderer,CSS2DObject} from 'three/addons/renderers/CSS2DRenderer.js';
import {GUI} from 'three/addons/libs/lil-gui.module.min.js';

//import Stats from 'three/addons/libs/stats.module'

document.getElementsByClassName("info")[0].addEventListener("touchstart",
 function(e) { e.returnValue = false });

let gui, labelRenderer, distances, orbitals;
var cur_orb = 0;
var cur_geom = 0;
var point_inside = 0x00ff00;
var point_outside = 0x01af00;
const colors = {
	'Charge in orbital': 0xff0000,
	'Charge outside orbital': 0x00ff00,
};
const isMobile = () => /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
distances = 1;

			const layers = {

				'Toggle Distances': true,
				
				'Toggle Mass': function () {

					scene.remove(orbitals);

				},
				'Toggle Axes': function () {

					camera.layers.toggle(1);

				},

				'Disable All': function () {

					camera.layers.disableAll();

				},
				
				'Distance': 1

			};

//Locations of each point for each geometry
//Order 'Octahedral': 0, 'Pentagonal bipyramidal': 1, 'Square antiprismatic': 2, 'Square planar': 3, 'Square pyramidal': 4, 'Tetrahedral': 5, 'Trigonal bipyramidal': 6
//Square antiprismatic geometry from https://polytope.miraheze.org/wiki/Square_antiprism
const point_geometries = [[[0,0,1],[0,0,-1],[0,1,0],[0,-1,0],[1,0,0],[-1,0,0]],
						  [[0,0,1],[0,0,-1],[1,0,0],[Math.cos(72*3.14159/180),Math.sin(72*3.14159/180),0],[Math.cos(144*3.14159/180),Math.sin(144*3.14159/180),0],[Math.cos(216*3.14159/180),Math.sin(216*3.14159/180),0],[Math.cos(288*3.14159/180),Math.sin(288*3.14159/180),0]],
						  [[1/2,1/2,Math.pow(8,1/4)/4],[1/2,-1/2,Math.pow(8,1/4)/4],[-1/2,1/2,Math.pow(8,1/4)/4],[-1/2,-1/2,Math.pow(8,1/4)/4],[0,Math.sqrt(2)/2,-Math.pow(8,1/4)/4],[0,-Math.sqrt(2)/2,-Math.pow(8,1/4)/4],[Math.sqrt(2)/2,0,-Math.pow(8,1/4)/4],[-Math.sqrt(2)/2,0,-Math.pow(8,1/4)/4]],
						  [[1,0,0],[-1,0,0],[0,1,0],[0,-1,0]],
						  [[1,0,0],[-1,0,0],[0,1,0],[0,-1,0],[0,0,1]],
						  [[0,0,1],[Math.sqrt(8/9),0,-1/3],[-Math.sqrt(2/9),Math.sqrt(2/3),-1/3],[-Math.sqrt(2/9),-Math.sqrt(2/3),-1/3]],
						  [[0,0,1],[0,0,-1],[Math.cos(120*3.14159/180),Math.sin(120*3.14159/180),0],[Math.cos(240*3.14159/180),Math.sin(240*3.14159/180),0],[Math.cos(360*3.14159/180),Math.sin(360*3.14159/180),0]],
						  []];

const scene = new THREE.Scene();
scene.background = new THREE.Color( 0xfafafa );
const camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 0.1, 1000 );
//set up camera so z is up
camera.up.set(0,0,1);
const renderer = new THREE.WebGLRenderer();
renderer.setPixelRatio( window.devicePixelRatio );
                      
renderer.setSize( window.innerWidth, window.innerHeight );                        
document.body.appendChild( renderer.domElement );  

const loader = new GLTFLoader();

const dxy = new THREE.Scene();
loader.load('dxy.glb',function( gltf ){
	prepare_orbital( gltf );			
	dxy.add(gltf.scene);
	
}, 	function (xhr) {
	console.log( 'dxy ' + ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
	},function( error ){
	console.log('An error occurred');
} );

const dyz = new THREE.Scene();
loader.load('dxy.glb',function( gltf ){
	//rotation defined in radians
	gltf.scene.rotation.y = 90*3.14159/180;
	
	prepare_orbital( gltf );		
	dyz.add(gltf.scene);
	
}, 	function (xhr) {
	console.log( 'dyz ' + ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
	},function( error ){
	console.log('An error occurred');
} );

const dzx = new THREE.Scene();
loader.load('dxy.glb',function( gltf ){
	//rotation defined in radians
	gltf.scene.rotation.x = 90*3.14159/180;
	
	prepare_orbital( gltf );	
	dzx.add(gltf.scene);
	
}, 	function (xhr) {
	console.log( 'dzx ' + ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
	},function( error ){
	console.log('An error occurred');
} );

const dx2y2 = new THREE.Scene();
loader.load('dxy.glb',function( gltf ){
	//rotation defined in radians
	gltf.scene.rotation.x = 90*3.14159/180;
	gltf.scene.rotation.z = 45*3.14159/180;
	
	prepare_orbital( gltf );	
	dx2y2.add(gltf.scene);
	
}, 	function (xhr) {
	console.log( 'dx2y2 ' + ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
	},function( error ){
	console.log('An error occurred');
} );

const dz2 = new THREE.Scene();
loader.load('dz2.glb',function( gltf ){
	prepare_orbital( gltf );	
	dz2.add(gltf.scene);	
}, 	function (xhr) {
	console.log( 'dz2 ' + ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
	},function( error ){
	console.log('An error occurred');
} );
const none = new THREE.Scene();
orbitals = [dxy,dyz,dzx,dx2y2,dz2,none];

//Add the x,y,z axis
//Length of the axis
const axis_size = 20

//define axis and their colors
const axeshelper = new THREE.AxesHelper(axis_size);
const axis_color_x = new THREE.Color('red');
const axis_color_y = new THREE.Color('green');
const axis_color_z = new THREE.Color('blue');
axeshelper.setColors(axis_color_x,axis_color_y,axis_color_z);

//define the axis labels
const x_axisDiv = document.createElement('div');
x_axisDiv.className = 'label';
x_axisDiv.textContent = 'X';
x_axisDiv.style.marginTop = '-1em';
const y_axisDiv = document.createElement('div');
y_axisDiv.className = 'label';
y_axisDiv.textContent = 'Y';
y_axisDiv.style.marginTop = '-1em';
const z_axisDiv = document.createElement('div');
z_axisDiv.className = 'label';
z_axisDiv.textContent = 'Z';
z_axisDiv.style.marginTop = '-1em';

//add labels to the axis
const x_axisLabel = new CSS2DObject(x_axisDiv);
const y_axisLabel = new CSS2DObject(y_axisDiv);
const z_axisLabel = new CSS2DObject(z_axisDiv);
x_axisLabel.position.set(axis_size,0,0);
y_axisLabel.position.set(0,axis_size,0);
z_axisLabel.position.set(0,0,axis_size);
x_axisLabel.layers.set(1);
y_axisLabel.layers.set(1);
z_axisLabel.layers.set(1);
axeshelper.add(x_axisLabel);
axeshelper.add(y_axisLabel);
axeshelper.add(z_axisLabel);
axeshelper.layers.set(1);	
scene.add(axeshelper);

//Set up label renderer
labelRenderer = new CSS2DRenderer();
labelRenderer.setSize(window.innerWidth,window.innerHeight);
labelRenderer.domElement.style.position = 'absolute';
labelRenderer.domElement.style.top = '0px';
document.body.appendChild( labelRenderer.domElement);

  window.addEventListener('resize', function()

    {
      var width = window.innerWidth;
      var height = window.innerHeight;
      renderer.setSize(width, height);
	  labelRenderer.setSize(width,height);
      camera.aspect = width / height;
      camera.updateProjectionMatrix();
    });


//Octahedral default geometry
const point_charges = new THREE.Scene();
for (let i = 0; i < 10; i++) {
	const geometry = new THREE.SphereGeometry( 0.5, 20, 20 );                        
	const material = new THREE.MeshBasicMaterial( { color: 0x00ff00 } );  
	point_charges.add( new THREE.Mesh (geometry, material) );
}
scene.add(point_charges); 
changeGeometry(cur_geom);
changeOrbital(cur_orb);


function changeGeometry(geom) {
	for (let i = 0; i < point_charges.children.length; i++) {
		console.log(point_charges.children[0]);
		point_charges.children[i].visible = false;
	}
	for (let i = 0; i < point_geometries[geom].length; i++) {
		point_charges.children[i].visible = true;
		point_charges.children[i].position.set(point_geometries[geom][i][0],point_geometries[geom][i][1],point_geometries[geom][i][2]);
	}
	cur_geom = geom;
}

                       
camera.position.z = 30;
camera.position.x = 30;
camera.position.y = 30;  
camera.layers.enableAll(); 
//Offset the scene for mobile readability
camera.setViewOffset(window.innerWidth,window.innerHeight,0,-0.2*window.innerHeight/2,window.innerWidth,window.innerHeight)                
function animate() {                                
	requestAnimationFrame( animate );
	labelRenderer.render(scene,camera);
	renderer.render( scene, camera );   
	//stats.update();
	updateDistances(distances);
	//console.log(isMobile());
};
const controls = new OrbitControls( camera, labelRenderer.domElement ); 

//For rendering stats
//const stats = Stats();
//document.body.appendChild(stats.dom)


const obj = { Geometry: 'Octahedral', Orbital: 'dxy' };


animate();
initGui();
function initGui() {
	if(isMobile()) {
		let gui_left;
		gui_left = new GUI({autoPlace: false});
		gui_left.title('');
		gui_left.$title.classList='';
		gui_left.add( obj, 'Geometry', { 'Octahedral': 0, 'Pentagonal bipyramidal': 1, 'Square antiprismatic': 2, 'Square planar': 3, 'Square pyramidal': 4, 'Tetrahedral': 5, 'Trigonal bipyramidal': 6, 'None': 7}).name('').onChange(value => changeGeometry(value));
		console.log(gui_left.children[0]);
		gui_left.children[0].$display.style.cssText += "margin-right: auto; margin-left: auto;";
		gui_left.children[0].$display.style.backgroundColor = "#222";
		//gui_left.children[0].initialValue = "Geometry";
		var gui_container_left = document.getElementById('gui-container-left');
		gui_container_left.appendChild(gui_left.domElement);
		
		let gui_middle;
		gui_middle = new GUI({autoPlace: false});
		gui_middle.title('');
		gui_middle.$title.classList='';
		gui_middle.add( layers, 'Distance', 0.01, 1 ).name('').onChange(val => {updateDistances(val)});
		gui_middle.children[0].$input.remove();
		var gui_container_middle = document.getElementById('gui-container-middle');
		gui_container_middle.appendChild(gui_middle.domElement);
		
		let gui_right;
		gui_right = new GUI({autoPlace: false});
		gui_right.title('');
		gui_right.$title.classList='';
		gui_right.add( obj, 'Orbital', { 'd<sub>xy</sub>': 0, 'd<sub>yz</sub>': 1, 'd<sub>zx</sub>': 2, 'd<sub>x<sup>2</sup>y<sup>2</sup></sub>': 3, 'd<sub>z<sup>2</sup></sub>': 4, None: 5} ).name('').onChange(value => changeOrbital(value));
		gui_right.children[0].$display.innerHTML = "d<sub>xy</sub>";
		console.log(gui_right.children[0])
		gui_right.children[0].$display.style.cssText += "margin-right: auto; margin-left: auto;"
		gui_right.children[0].$display.style.backgroundColor = "#222";
		var gui_container_right = document.getElementById('gui-container-right');
		gui_container_right.appendChild(gui_right.domElement);
	} else {
		gui = new GUI();
		gui.add( layers, 'Toggle Axes' );
		gui.add( layers, 'Distance', 0.01, 1 ).onChange(val => {updateDistances(val)});
		gui.add( obj, 'Geometry', { 'Octahedral': 0, 'Pentagonal bipyramidal': 1, 'Square antiprismatic': 2, 'Square planar': 3, 'Square pyramidal': 4, 'Tetrahedral': 5, 'Trigonal bipyramidal': 6 }).onChange(value => changeGeometry(value));
		gui.add( obj, 'Orbital', { dxy: 0, dyz: 1, dzx: 2, dx2y2: 3, dz2: 4 } ).onChange(value => changeOrbital(value));
		gui.addColor( colors, 'Charge in orbital');
		gui.addColor( colors, 'Charge outside orbital');
	};
}
//let gui_right;
//gui_right = new GUI({autoPlace: false});
//var gui_container_right = document.getElementById('gui-container-left');
//gui_container_right.appendChild(gui_right.domElement);

function changeOrbital(val) {
	cur_orb = val;
	for (let i = 0; i < orbitals.length; i++) {
		scene.remove(orbitals[i]);
	}
	scene.add(orbitals[val]);
	for (let i = 0; i < point_charges.children.length; i++) {
		if (inOrbital(i) == 1) {
			point_charges.children[i].material.color.setHex(colors['Charge in orbital']);
		} else {
			point_charges.children[i].material.color.setHex(colors['Charge outside orbital']);
		};
	}
}

function updateDistances(val) {
	for (let i = 0; i < point_charges.children.length; i++) {
		point_charges.children[i].position.setLength(val*20);
		if (inOrbital(i) == 1) {
			point_charges.children[i].material.color.setHex(colors['Charge in orbital']);
		} else {
			point_charges.children[i].material.color.setHex(colors['Charge outside orbital']);
		};
	}
	distances = val;
}

function renderOrderUpdate() {
	console.log('hello');
}

function prepare_orbital(orbit) {
	//Center orbital
	const box = new THREE.Box3().setFromObject( orbit.scene );
	const center = box.getCenter( new THREE.Vector3() );
	orbit.scene.position.x += ( orbit.scene.position.x - center.x );
	orbit.scene.position.y += ( orbit.scene.position.y - center.y );
	orbit.scene.position.z += ( orbit.scene.position.z - center.z );
	for (let i = 0; i< orbit.scene.children.length; i++) {
		//Set up orbital material
		const material = new THREE.MeshStandardMaterial({
			blending: THREE.NormalBlending,
			side: THREE.TwoPassDoubleSide,
			transparent: true,
			opacity: 0.3,
		})
		orbit.scene.children[i].material = material;
	}
}

function inOrbital(point) {
	const raycaster = new THREE.Raycaster()
	raycaster.set(point_charges.children[point].position, new THREE.Vector3(0,0,1))
	const intersects = raycaster.intersectObject(orbitals[cur_orb])
	return (intersects.length %2)
}
</script>



</body>
</html>
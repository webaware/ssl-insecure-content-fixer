module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

		clean: [ "dist/**" ],

		copy: {
			main: {
				files: [
					{
						src: [
							"./**",
							"!./es6/**",
							"!./node_modules/**",
							"!./vendor/**",
							"!./composer.*",
							"!./Gruntfile.js",
							"!./package*.json",
							"!./phpcs*.xml",
						],
						dest: "dist/<%= pkg.name %>/"
					}
				]
			}
		},

		compress: {
			options: {
				archive: "./dist/<%= pkg.name %>-<%= pkg.version %>.zip",
				mode: "zip"
			},
			all: {
				files: [{
					expand: true,
					cwd: "./dist/",
					date: new Date(),
					src: [ "<%= pkg.name %>/**" ]
				}]
			}
		},

		eslint: {
			all: [
				"Gruntfile.js",
				"es6/*.js"
			]
		},

		babel: {
			options: {
				presets: [
					'@babel/preset-env',
				]
			},
			dist: {
				files: [{
					"expand": true,
					"cwd": "es6",
					"src": ["**/*.js"],
					"dest": "js/",
					"ext": ".js",
				}]
			}
		},

		uglify: {
			build: {
				options: {
					output: {
						ascii_only: true,
					},
					banner: "// <%= pkg.description %>\n// <%= pkg.homepage %>\n"
				},
				files: [{
					expand: true,
					cwd: "js",
					dest: "js",
					src: [
						"*.js",
						"!*.min.js"
					],
					ext: '.min.js'
				}]
			}
		}

	});

	grunt.loadNpmTasks("grunt-babel");
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks("grunt-contrib-compress");
	grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-contrib-uglify-es");
	grunt.loadNpmTasks("grunt-eslint");

	grunt.registerTask("release", ["clean","copy","compress"]);
	grunt.registerTask("es6", ["babel","uglify"]);

};

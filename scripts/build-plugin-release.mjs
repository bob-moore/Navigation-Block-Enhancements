#!/usr/bin/env node

import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import process from 'node:process';

const rootDir = process.cwd();
const composerJson = readJson( path.join( rootDir, 'composer.json' ) );
const pluginFolder = getPluginFolderName();
const releaseRoot = path.join( os.tmpdir(), `${ pluginFolder }-release` );
const stagingDir = path.join( releaseRoot, pluginFolder );
const zipName = `${ pluginFolder }.zip`;
const zipPath = path.join( rootDir, zipName );

const distributablePaths = composerJson.extra?.[ 'plugin-release' ]?.files ?? [];

main();

function getPluginFolderName() {
	const packageName = composerJson.name ?? path.basename( rootDir );
	const packageSlug = packageName.split( '/' ).pop() ?? packageName;

	return packageSlug.replaceAll( /[^a-z0-9._-]/gi, '-' );
}

function main() {
	cleanDirectory( releaseRoot );
	fs.mkdirSync( stagingDir, { recursive: true } );

	copyDistributableFiles();
	writeReleaseComposerDepsJson();
	writeReleaseComposerJson();

	runWpifyScoper();
	patchPluginSourceForScopedRuntime();
	createEmptyCacheDir();
	removeBuildOnlyFiles();
	createZip();

	console.log( `\nRelease zip ready: ${ zipName }` );
}

function copyDistributableFiles() {
	for ( const relativePath of distributablePaths ) {
		const source = path.join( rootDir, relativePath );
		const destination = path.join( stagingDir, relativePath );

		if ( ! fs.existsSync( source ) ) {
			continue;
		}

		fs.cpSync( source, destination, {
			recursive: true,
			filter: ( currentSource ) =>
				! currentSource.includes( `${ path.sep }.DS_Store` ),
		} );
	}
}

function writeReleaseComposerDepsJson() {
	const config = { 'optimize-autoloader': true };

	if ( composerJson.config?.platform ) {
		config.platform = composerJson.config.platform;
	}

	if ( composerJson.config?.[ 'allow-plugins' ] ) {
		config[ 'allow-plugins' ] = composerJson.config[ 'allow-plugins' ];
	}

	// Merge local composer-deps.json so packages listed there are scoped into
	// the release without being public Composer dependencies.
	const localDeps = readJsonIfExists( path.join( rootDir, 'composer-deps.json' ) );

	const deps = {
		name: `${ composerJson.name }-dependencies`,
		description: `Dependencies for ${ composerJson.name }`,
		config,
		require: { ...( composerJson.require ?? {} ), ...( localDeps?.require ?? {} ) },
	};

	if ( composerJson[ 'minimum-stability' ] !== undefined ) {
		deps[ 'minimum-stability' ] = composerJson[ 'minimum-stability' ];
	}

	if ( composerJson[ 'prefer-stable' ] !== undefined ) {
		deps[ 'prefer-stable' ] = composerJson[ 'prefer-stable' ];
	}

	writeJson( path.join( stagingDir, 'composer-deps.json' ), deps );
}

function writeReleaseComposerJson() {
	const releaseComposer = structuredClone( composerJson );

	// Production deps are scoped via composer-deps.json — not needed in require.
	delete releaseComposer.require;

	// wpify/scoper drives the scoping during composer install; stripped by --no-dev after.
	const wpifyScoperVersion = composerJson[ 'require-dev' ]?.[ 'wpify/scoper' ] ?? '^3.2';
	releaseComposer[ 'require-dev' ] = { 'wpify/scoper': wpifyScoperVersion };

	delete releaseComposer[ 'autoload-dev' ];
	delete releaseComposer.scripts;

	if ( releaseComposer.extra?.[ 'wpify-scoper' ] ) {
		releaseComposer.extra[ 'wpify-scoper' ].autorun = true;
		releaseComposer.extra[ 'wpify-scoper' ].composerjson = 'composer-deps.json';
	}

	writeJson( path.join( stagingDir, 'composer.json' ), releaseComposer );
}

function runWpifyScoper() {
	// Full install: resolves deps, locks them, and wpify/scoper autorun creates vendor/scoped.
	run( 'composer', [
		'install',
		`--working-dir=${ stagingDir }`,
		'--optimize-autoloader',
	], {
		label: 'composer install (with scoping)',
	} );

	// Strip dev deps (wpify/scoper and its tree) — vendor/scoped is already built.
	run( 'composer', [
		'install',
		`--working-dir=${ stagingDir }`,
		'--no-dev',
		'--optimize-autoloader',
	], {
		label: 'composer install --no-dev (strip build tools)',
	} );
}


function patchPluginSourceForScopedRuntime() {
	const prefix = composerJson.extra?.[ 'wpify-scoper' ]?.prefix ?? '';
	const namespacesToPatch = composerJson.extra?.[ 'wpify-scoper' ]?.[ 'source-namespace-patches' ] ?? [];

	const replacements = new Map(
		namespacesToPatch.map( ( ns ) => [ ns, `${ prefix }\\${ ns }` ] )
	);

	if ( 0 === replacements.size ) {
		return;
	}

	const phpFiles = [
		...fs.readdirSync( stagingDir, { withFileTypes: true } )
			.filter( ( e ) => e.isFile() && e.name.endsWith( '.php' ) )
			.map( ( e ) => path.join( stagingDir, e.name ) ),
		...listFiles( path.join( stagingDir, 'inc' ) ).filter( ( f ) => f.endsWith( '.php' ) ),
	];

	for ( const filePath of phpFiles ) {
		let contents = fs.readFileSync( filePath, 'utf8' );

		for ( const [ search, replace ] of replacements ) {
			contents = contents.split( search ).join( replace );
		}

		fs.writeFileSync( filePath, contents );
	}
}

function createEmptyCacheDir() {
	const cacheDir = path.join( stagingDir, 'cache' );
	cleanDirectory( cacheDir );
}

function removeBuildOnlyFiles() {
	// Strip build-only Composer files from the distribution.
	const composerJsonPath = path.join( stagingDir, 'composer.json' );
	const releaseComposerJson = readJson( composerJsonPath );
	delete releaseComposerJson[ 'require-dev' ];
	if ( releaseComposerJson.extra?.[ 'wpify-scoper' ] ) {
		delete releaseComposerJson.extra[ 'wpify-scoper' ].composerjson;
	}
	writeJson( composerJsonPath, releaseComposerJson );

	// Remove build-only files not needed in the distribution.
	for ( const relativePath of [ 'composer-deps.json', 'composer-deps.lock', 'composer.json', 'composer.lock' ] ) {
		fs.rmSync( path.join( stagingDir, relativePath ), { force: true } );
	}
}

function createZip() {
	fs.rmSync( zipPath, { force: true } );

	run( 'zip', [ '-r', zipPath, pluginFolder ], {
		cwd: releaseRoot,
		label: 'create plugin zip',
	} );
}

function cleanDirectory( directory ) {
	fs.rmSync( directory, {
		force: true,
		recursive: true,
	} );
	fs.mkdirSync( directory, { recursive: true } );
}

function readJson( filePath ) {
	return JSON.parse( fs.readFileSync( filePath, 'utf8' ) );
}

function readJsonIfExists( filePath ) {
	return fs.existsSync( filePath ) ? readJson( filePath ) : null;
}

function writeJson( filePath, value ) {
	fs.writeFileSync( filePath, `${ JSON.stringify( value, null, '\t' ) }\n` );
}

function run( command, args, { label, cwd } = {} ) {
	console.log( `\n> ${ label }` );

	const result = spawnSync( command, args, {
		cwd,
		shell: false,
		stdio: 'inherit',
	} );

	if ( 0 !== result.status ) {
		throw new Error( `${ label } failed.` );
	}
}

function listFiles( directory ) {
	if ( ! fs.existsSync( directory ) ) {
		return [];
	}

	return fs.readdirSync( directory, { withFileTypes: true } ).flatMap(
		( entry ) => {
			const entryPath = path.join( directory, entry.name );

			return entry.isDirectory() ? listFiles( entryPath ) : [ entryPath ];
		}
	);
}

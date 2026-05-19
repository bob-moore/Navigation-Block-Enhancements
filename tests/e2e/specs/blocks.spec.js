const { test, expect } = require( '@playwright/test' );

const username = process.env.WP_USERNAME || 'admin';
const password = process.env.WP_PASSWORD || 'password';

async function login( page ) {
	await page.goto( '/wp-login.php' );

	if ( await page.locator( '#wpadminbar' ).count() ) {
		return;
	}

	await page.locator( '#user_login' ).fill( username );
	await page.locator( '#user_pass' ).fill( password );

	await Promise.all( [
		page.waitForURL( /wp-admin/, { waitUntil: 'networkidle' } ),
		page.locator( '#wp-submit' ).click(),
	] );
}

async function waitForEditor( page ) {
	await page.waitForFunction(
		() =>
			window.wp?.blocks &&
			window.wp?.data?.select( 'core/editor' ) &&
			window.wp?.data?.select( 'core/block-editor' ),
		undefined,
		{ timeout: 30000 }
	);
}

test.describe( 'Example blocks', () => {
	test( 'register, save, and render on the frontend', async ( { page } ) => {
		const title = `E2E example blocks ${ Date.now() }`;

		await login( page );
		await page.goto( '/wp-admin/post-new.php' );
		await waitForEditor( page );

		const postUrl = await page.evaluate( async ( postTitle ) => {
			const { blocks, data } = window.wp;
			const blockNames = [
				'bmd/example-block',
				'bmd/example-block-2',
			];

			for ( const blockName of blockNames ) {
				if ( ! blocks.getBlockType( blockName ) ) {
					throw new Error( `${ blockName } is not registered.` );
				}
			}

			data.dispatch( 'core/editor' ).editPost( {
				title: postTitle,
				status: 'publish',
			} );
			data.dispatch( 'core/block-editor' ).resetBlocks(
				blockNames.map( ( blockName ) =>
					blocks.createBlock( blockName )
				)
			);

			const editedContent = data
				.select( 'core/editor' )
				.getEditedPostContent();

			for ( const blockName of blockNames ) {
				if ( ! editedContent.includes( `<!-- wp:${ blockName }` ) ) {
					throw new Error( `${ blockName } was not inserted.` );
				}
			}

			await data.dispatch( 'core/editor' ).savePost();

			return data.select( 'core/editor' ).getPermalink();
		}, title );

		await page.waitForFunction(
			() =>
				! window.wp.data.select( 'core/editor' ).isSavingPost() &&
				! window.wp.data.select( 'core/editor' ).isAutosavingPost(),
			undefined,
			{ timeout: 30000 }
		);

		await page.goto( postUrl );

		await expect( page.locator( 'body' ) ).toContainText( title );
		await expect(
			page.locator( 'main p', { hasText: 'Hello World' } )
		).toHaveCount( 2 );
	} );
} );

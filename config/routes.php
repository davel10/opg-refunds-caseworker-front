<?php
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

//  Unauthenticated routes
$app->route('/sign-in', App\Action\SignInAction::class, ['GET', 'POST'], 'sign.in');
$app->get('/sign-out', App\Action\SignOutAction::class, 'sign.out');
$app->get('/reset-password', App\Action\Password\PasswordResetAction::class, 'password.reset');

//  Authenticated routes - see AuthorizationMiddleware
$app->get('/', App\Action\Home\HomeAction::class, 'home');
$app->get('/user[/{id:\d+}]', App\Action\User\UserAction::class, 'user');
$app->route('/user/add', App\Action\User\UserUpdateAction::class, ['GET', 'POST'], 'user.add');
$app->route('/user/edit/{id:\d+}', App\Action\User\UserUpdateAction::class, ['GET', 'POST'], 'user.edit');
$app->route('/user/delete/{id:\d+}', App\Action\User\UserDeleteAction::class, ['GET', 'POST'], 'user.delete');
$app->get('/refund', App\Action\RefundAction::class, 'refund');
$app->get('/reporting', App\Action\ReportingAction::class, 'reporting');
$app->route('/change-password[/{token}]', App\Action\Password\PasswordChangeAction::class, ['GET', 'POST'], 'password.change');
$app->get('/download[/{date:\d{4}-\d{2}-\d{2}}]', App\Action\DownloadAction::class, 'download');
$app->get('/csv-download', App\Action\CsvDownloadAction::class, 'csv.download');
$app->route('/claim[/{id:\d+}]', App\Action\Claim\ClaimAction::class, ['GET', 'POST'], 'claim');
$app->route('/claim/{claimId:\d+}/approve', App\Action\Claim\ClaimApproveAction::class, ['GET', 'POST'], 'claim.approve');
$app->route('/claim/{claimId:\d+}/reject', App\Action\Claim\ClaimRejectAction::class, ['GET', 'POST'], 'claim.reject');
$app->route('/claim/{claimId:\d+}/poa/{system:sirius|meris}[/{id:\d+}]', App\Action\Poa\PoaAction::class, ['GET', 'POST'], 'claim.poa');
$app->post('/claim/{id:\d+}/poa/{system:sirius|meris}/none-found', App\Action\Poa\PoaNoneFoundAction::class, 'claim.poa.none.found');
$app->route('/claim/{claimId:\d+}/poa/{system:sirius|meris}/{id:\d+}/delete', App\Action\Poa\PoaDeleteAction::class, ['GET', 'POST'], 'claim.poa.delete');

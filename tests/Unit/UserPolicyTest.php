<?php

namespace Tests\Unit;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The password policy is applied in two places — an admin creating or
 * resetting an account, and a user setting their own password at first
 * sign-in. They now share one implementation; these pin its behaviour.
 */
final class UserPolicyTest extends CIUnitTestCase
{
    public function testAStrongPasswordIsAccepted(): void
    {
        $this->assertSame([], UserModel::passwordProblems('GoodPass1!long'));
    }

    public function testTooShortIsRejected(): void
    {
        $problems = UserModel::passwordProblems('Ab1!xyz');

        $this->assertNotSame([], $problems);
        $this->assertStringContainsString('at least 12', $problems[0]);
    }

    public function testEachMissingCharacterClassIsReported(): void
    {
        $this->assertStringContainsString('uppercase', implode(' ', UserModel::passwordProblems('lowercase1!xx')));
        $this->assertStringContainsString('number', implode(' ', UserModel::passwordProblems('NoDigitsHere!!')));
        $this->assertStringContainsString('symbol', implode(' ', UserModel::passwordProblems('NoSymbols1234')));
    }

    public function testAnEmptyPasswordFailsEveryCheck(): void
    {
        $this->assertCount(4, UserModel::passwordProblems(''));
    }

    public function testTheLengthIsCountedInBytesNotCharacters(): void
    {
        // strlen() is deliberate: the limit is a floor, and counting bytes can
        // only ever be more generous to a password using multi-byte symbols.
        $this->assertSame([], UserModel::passwordProblems('Pässwörd1!äö'));
    }

    public function testHashingProducesAVerifiableBcryptHash(): void
    {
        $hash = UserModel::hash('GoodPass1!long');

        $this->assertNotSame('GoodPass1!long', $hash);
        $this->assertTrue(password_verify('GoodPass1!long', $hash));
        $this->assertFalse(password_verify('GoodPass1!wrong', $hash));
    }

    public function testOnlyTwoRolesExistAndAdminIsOne(): void
    {
        // Admin\AdminController::requireRole compares against 'admin'; a typo
        // in this list would silently grant or deny access everywhere.
        $this->assertSame(['admin', 'editor'], array_keys(UserModel::ROLES));
    }

    public function testEveryRoleIsDescribedForTheForm(): void
    {
        foreach (UserModel::ROLES as $role => $label) {
            $this->assertNotSame('', $label, "role '{$role}' has no description");
        }
    }

    public function testAccountLockoutIsRecognisedFromItsExpiry(): void
    {
        $model = new UserModel();

        $this->assertTrue($model->isLocked(['locked_until' => date('Y-m-d H:i:s', time() + 600)]));
        $this->assertFalse($model->isLocked(['locked_until' => date('Y-m-d H:i:s', time() - 600)]));
        $this->assertFalse($model->isLocked(['locked_until' => null]));
        $this->assertFalse($model->isLocked([]));
    }
}

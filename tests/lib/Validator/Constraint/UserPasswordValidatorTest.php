<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformUser\Tests\Validator\Constraint;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\MVC\Symfony\Security\ReferenceUserInterface;
use EzSystems\EzPlatformUser\Validator\Constraints\UserPassword;
use EzSystems\EzPlatformUser\Validator\Constraints\UserPasswordValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserPasswordValidatorTest extends TestCase
{
    /**
     * @var UserService|MockObject
     */
    private $userService;

    /**
     * @var TokenStorageInterface|MockObject
     */
    private $tokenStorage;

    /**
     * @var ExecutionContextInterface|MockObject
     */
    private $executionContext;

    /**
     * @var UserPasswordValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new UserPasswordValidator($this->userService, $this->tokenStorage);
        $this->validator->initialize($this->executionContext);
    }

    /**
     * @dataProvider emptyDataProvider
     *
     * @param string|null $value
     */
    public function testEmptyValueType($value)
    {
        $this->userService
            ->expects($this->never())
            ->method('checkUserCredentials');
        $this->tokenStorage
            ->expects($this->never())
            ->method('getToken');
        $this->executionContext
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, new UserPassword());
    }

    public function emptyDataProvider(): array
    {
        return [
            'empty_string' => [''],
            'null' => [null],
        ];
    }

    public function testValid()
    {
        $apiUser = $this->getMockForAbstractClass(APIUser::class, [], '', true, true, true, ['__get']);
        $apiUser->method('__get')->with($this->equalTo('login'))->willReturn('login');
        $user = $this->createMock(ReferenceUserInterface::class);
        $user->method('getAPIUser')->willReturn($apiUser);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->userService
            ->method('checkUserCredentials')
            ->with($apiUser, 'password')
            ->willReturn(true);
        $this->executionContext
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate('password', new UserPassword());
    }

    public function testInvalid()
    {
        $apiUser = $this->getMockForAbstractClass(APIUser::class, [], '', true, true, true, ['__get']);
        $apiUser->method('__get')->with($this->equalTo('login'))->willReturn('login');
        $user = $this->createMock(ReferenceUserInterface::class);
        $user->method('getAPIUser')->willReturn($apiUser);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->userService
            ->method('checkUserCredentials')
            ->with($apiUser, 'password')
            ->willReturn(false);
        $constraint = new UserPassword();
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($constraintViolationBuilder);

        $this->validator->validate('password', new UserPassword());
    }
}

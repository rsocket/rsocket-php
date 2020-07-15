<?php


use PHPUnit\Framework\TestCase;
use RSocket\routing\JsonSupport;
use RSocket\routing\RSocketServiceRouter;

class User extends JsonSupport
{
    public string $nick;
    public int $age;
    public string $city;
}

class UserServiceImpl
{
    public function findAll(): array
    {
        return ["first", "second"];
    }

    public function findNickById(int $id): string
    {
        return "nick";
    }

    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }

    public function findUserByNick(string $nick): User
    {
        $user = new User();
        $user->nick = $nick;
        $user->age = 40;
        $user->city = "San Francisco";
        return $user;
    }
}


class ServerRoutingTest extends TestCase
{

    public function testUserJson(): void
    {
        $userService = new UserServiceImpl();
        $user = $userService->findUserByNick("linux_china");
        $jsonText = $user->toJson();
        print($jsonText);
        $jsonText = '{"nick":"linux_china","age":40,"city":"San Francisco", "score": 2}';
        $result = json_decode($jsonText, true);
        $user2 = new User();
        $user2->loadFromJson($result);
        self::assertEquals($user->city, $user2->city);
    }

    public function testRouting(): void
    {
        $userService = new UserServiceImpl();
        $names = ([$userService, "findAll"])();
        self::assertEquals(["first", "second"], $names);
        var_dump($names);
    }

    public function testSum(): void
    {
        $userService = new UserServiceImpl();
        $result = ([$userService, "sum"])(...[1, 2]);
        self::assertEquals(3, $result);
        print($result);
    }

    public function testRoutingParam(): void
    {
        RSocketServiceRouter::addService("UserService", new UserServiceImpl());
        $result = RSocketServiceRouter::invoke("UserService", "findNickById", 1);
        self::assertEquals("nick", $result);
    }

    public function testRoutineParams(): void
    {
        RSocketServiceRouter::addService("UserService", new UserServiceImpl());
        $result = RSocketServiceRouter::invoke("UserService", "sum", [1, 2]);
        self::assertEquals(3, $result);
        var_dump($result);
    }

}
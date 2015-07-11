<?php

namespace ProcRunner;

interface RunnerInterface
{
    public function isAlive();
    public function send($string);
    public function readAll();
    public function readLine($timeout = null);
    public function readLineIfAny();
    public function readStderrLine($timeout = null);
    public function readStderrLineIfAny();
    public function close();
    public function getReturnValue();
}
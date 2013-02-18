<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Fixture\Sorter;

/**
 * Contract required for any possible list of fixture reordering.
 * This class implements the Visitor pattern to identity (accept) and process
 * (visit) a list of fixtures.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
interface Calculator
{
    /**
     * Checks if calculator accepts this list of fixtures for reordering.
     *
     * {@internal Any implementor algorithm should not exceed O(n) for
     *            performance reasons.}
     *
     * @param array<Doctrine\Fixture\Fixture> $fixtureList
     *
     * @return boolean
     */
    function accept(array $fixtureList);

    /**
     * Processes the reordering a given list of fixtures and returns the
     * reordered list.
     *
     * @param array<Doctrine\Fixture\Fixture> $fixtureList
     *
     * @return array<Doctrine\Fixture\Fixture>
     */
    function calculate(array $fixtureList);
}

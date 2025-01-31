<?php

/*
 * Copyright 2005 - 2023 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\ResourceAccess\Infrastructure\API\AddRule;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\ResourceAccess\Application\Exception\RuleException;
use Core\ResourceAccess\Application\UseCase\AddRule\AddRule;
use Core\ResourceAccess\Application\UseCase\AddRule\AddRuleRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class AddRuleController extends AbstractController
{
    use LoggerTrait;

    /**
     * @param Request $request
     * @param AddRule $useCase
     * @param AddRulePresenter $presenter
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(
        Request $request,
        AddRule $useCase,
        AddRulePresenter $presenter
    ): Response {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        try {
            /**
             * @var array{
             *     name: string,
             *     description?: string,
             *     is_enabled?: bool,
             *     contacts: non-empty-list<int>,
             *     contact_groups: non-empty-list<int>,
             *     dataset_filters: non-empty-list<array{
             *      type:string,
             *      resources: non-empty-list<int>,
             *      ...
             *     }>
             * } $data
             */
            $data = $this->validateAndRetrieveDataSent($request, __DIR__ . '/AddRuleSchema.yaml');

            $requestDto = new AddRuleRequest();
            $requestDto->name = $data['name'];
            $requestDto->isEnabled = $data['is_enabled'] ?? true;
            $requestDto->description = $data['description'] ?? '';
            $requestDto->contactIds = $data['contacts'];
            $requestDto->contactGroupIds = $data['contact_groups'];
            $requestDto->datasetFilters = $data['dataset_filters'];

            $useCase($requestDto, $presenter);
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $presenter->setResponseStatus(new InvalidArgumentResponse($exception));
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $presenter->setResponseStatus(new ErrorResponse(RuleException::addRule()));
        }

        return $presenter->show();
    }
}

import {
  isEmpty,
  T,
  always,
  cond,
  equals,
  flatten,
  groupBy,
  identity,
  includes,
  pipe,
  map
} from 'ramda';

import { centreonBaseURL } from '@centreon/ui';

import { Resource } from './models';

export const areResourcesFullfilled = (
  resourcesDataset: Array<Resource>
): boolean =>
  !isEmpty(resourcesDataset) &&
  resourcesDataset?.every(
    ({ resourceType, resources }) =>
      !isEmpty(resourceType) && !isEmpty(resources)
  );

const serviceCriteria = {
  name: 'resource_types',
  value: [{ id: 'service', name: 'Service' }]
};

interface GetResourcesUrlProps {
  allResources: Array<Resource>;
  isForOneResource: boolean;
  resource;
  states: Array<string>;
  statuses: Array<string>;
  type: string;
}

export const getDetailsPanelQueriers = ({ resource }): object => {
  const { id, parentId, uuid, type } = resource;

  const resourcesDetailsEndpoint = equals(type, 'host')
    ? `${centreonBaseURL}/api/latest/monitoring/resources/hosts/${id}`
    : `${centreonBaseURL}/api/latest/monitoring/resources/hosts/${parentId}/services/${id}`;

  const queryParameters = {
    id,
    resourcesDetailsEndpoint,
    selectedTimePeriodId: 'last_24_h',
    tab: 'details',
    tabParameters: {},
    uuid
  };

  return queryParameters;
};

export const getResourcesUrl = ({
  type,
  statuses,
  states,
  allResources,
  isForOneResource,
  resource
}: GetResourcesUrlProps): string => {
  const resourcesCriterias = equals(type, 'all')
    ? {
        name: 'resource_types',
        value: [
          { id: 'service', name: 'Service' },
          { id: 'host', name: 'Host' }
        ]
      }
    : {
        name: 'resource_types',
        value: [
          { id: type, name: `${type.charAt(0).toUpperCase()}${type.slice(1)}` }
        ]
      };

  const formatStatusFilter = cond([
    [equals('success'), always(['ok', 'up'])],
    [equals('problem'), always(['down', 'critical'])],
    [equals('undefined'), always(['unreachable', 'unknown'])],
    [T, identity]
  ]);

  const formattedStatuses = pipe(
    map((status) => formatStatusFilter(status)),
    flatten,
    map((status: string) => {
      return {
        id: status.toLocaleUpperCase(),
        name: `${status.charAt(0).toUpperCase()}${status.slice(1)}`
      };
    })
  )(statuses);

  const formattedStates = states.map((state) => {
    return {
      id: state,
      name: `${state.charAt(0).toUpperCase()}${state.slice(1)}`
    };
  });

  const groupedResources = groupBy(
    ({ resourceType }) => resourceType,
    allResources
  );

  const resourcesFilters = Object.entries(groupedResources).map(
    ([resourceType, res]) => {
      const name = cond<Array<string>, string>([
        [equals('host'), always('parent_name')],
        [equals('service'), always('name')],
        [T, identity]
      ])(resourceType);

      return {
        name: name.replace('-', '_'),
        value: flatten(
          (res || []).map(({ resources: subResources }) => {
            return subResources.map(({ name: resourceName }) => ({
              id: includes(name, ['name', 'parent_name'])
                ? `\\b${resourceName}\\b`
                : resourceName,
              name: resourceName
            }));
          })
        )
      };
    }
  );

  const filterQueryParameter = {
    criterias: [
      resourcesCriterias,
      { name: 'statuses', value: formattedStatuses },
      { name: 'states', value: formattedStates },
      ...resourcesFilters,
      { name: 'search', value: '' }
    ]
  };

  const encodedFilterParams = encodeURIComponent(
    JSON.stringify(filterQueryParameter)
  );

  if (!isForOneResource) {
    return `/monitoring/resources?filter=${encodedFilterParams}&fromTopCounter=true`;
  }

  const detailsPanelQueriers = getDetailsPanelQueriers({ resource });

  const encodedDetailsParams = encodeURIComponent(
    JSON.stringify(detailsPanelQueriers)
  );

  return `/monitoring/resources?details=${encodedDetailsParams}&filter=${encodedFilterParams}&fromTopCounter=true`;
};

const getDetailsPanelQueriersForMetricsWidgets = (data): object => {
  const uuid = data?.uuid;
  const hostId = uuid?.split('-')[0]?.slice(1);
  const serviceId = uuid?.split('-')[1]?.slice(1);

  const resourcesDetailsEndpoint = `${centreonBaseURL}/api/latest/monitoring/resources/hosts/${hostId}/services/${serviceId}`;

  const queryParameters = {
    id: serviceId,
    resourcesDetailsEndpoint,
    selectedTimePeriodId: 'last_24_h',
    tab: 'details',
    tabParameters: {},
    uuid
  };

  return queryParameters;
};

export const getResourcesUrlForMetricsWidgets = (data): string => {
  const filters = [
    {
      name: 'name',
      value: [
        {
          id: `\\b${data?.name}\\b`,
          name: data?.name
        }
      ]
    },
    {
      name: 'h.name',
      value: [
        {
          id: `\\b${data?.parentName}\\b`,
          name: data?.parentName
        }
      ]
    }
  ];

  const filterQueryParameter = {
    criterias: [serviceCriteria, ...filters, { name: 'search', value: '' }]
  };

  const encodedFilterParams = encodeURIComponent(
    JSON.stringify(filterQueryParameter)
  );

  const detailsPanelQueriers = getDetailsPanelQueriersForMetricsWidgets(data);
  const encodedDetailsParams = encodeURIComponent(
    JSON.stringify(detailsPanelQueriers)
  );

  return `/monitoring/resources?details=${encodedDetailsParams}&filter=${encodedFilterParams}&fromTopCounter=true`;
};

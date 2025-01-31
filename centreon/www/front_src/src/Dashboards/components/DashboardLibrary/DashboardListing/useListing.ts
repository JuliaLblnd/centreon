import { useState } from 'react';

import { useAtom, useSetAtom } from 'jotai';
import { equals } from 'ramda';

import {
  Dashboard,
  DashboardRole,
  FormattedDashboard,
  ShareType
} from '../../../api/models';
import { useDeleteAccessRightsContact } from '../../../api/useDeleteAccessRightsContact';
import { useDeleteAccessRightsContactGroup } from '../../../api/useDeleteAccessRightsContactGroup';

import {
  pageAtom,
  limitAtom,
  sortOrderAtom,
  sortFieldAtom,
  selectedRowsAtom,
  askBeforeRevokeAtom
} from './atom';
import { formatListingData } from './utils';

interface ConfirmRevokeAccessRightProps {
  dashboardId: string | number;
  id: string | number;
  type: ShareType;
}

interface UseListing {
  changePage: (updatedPage: number) => void;
  changeSort: ({ sortOrder, sortField }) => void;
  closeAskRevokeAccessRight: () => void;
  confirmRevokeAccessRight: (
    props: ConfirmRevokeAccessRightProps
  ) => () => void;
  formattedRows: Array<FormattedDashboard>;
  getRowProperty: (row) => string;
  page?: number;
  resetColumns: () => void;
  selectedColumnIds: Array<string>;
  selectedRows: Array<Dashboard>;
  setLimit;
  setSelectedColumnIds;
  setSelectedRows;
  sortf: string;
  sorto: 'asc' | 'desc';
}

const useListing = ({
  defaultColumnsIds,
  rows
}: {
  defaultColumnsIds: Array<string>;
  rows?: Array<Dashboard>;
}): UseListing => {
  const [selectedColumnIds, setSelectedColumnIds] =
    useState<Array<string>>(defaultColumnsIds);

  const resetColumns = (): void => {
    setSelectedColumnIds(defaultColumnsIds);
  };

  const [selectedRows, setSelectedRows] = useAtom(selectedRowsAtom);
  const [sorto, setSorto] = useAtom(sortOrderAtom);
  const [sortf, setSortf] = useAtom(sortFieldAtom);
  const [page, setPage] = useAtom(pageAtom);
  const setLimit = useSetAtom(limitAtom);
  const setAskingBeforeRevoke = useSetAtom(askBeforeRevokeAtom);

  const { mutate: deleteAccessRightContact } = useDeleteAccessRightsContact();
  const { mutate: deleteAccessRightContactGroup } =
    useDeleteAccessRightsContactGroup();

  const changeSort = ({ sortOrder, sortField }): void => {
    setSortf(sortField);
    setSorto(sortOrder);
  };

  const changePage = (updatedPage): void => {
    setPage(updatedPage + 1);
  };

  const getRowProperty = (row): string => {
    if (equals(row?.ownRole, DashboardRole.viewer)) {
      return '';
    }

    return 'shares';
  };

  const closeAskRevokeAccessRight = (): void => {
    setAskingBeforeRevoke(null);
  };

  const confirmRevokeAccessRight =
    ({ dashboardId, id, type }: ConfirmRevokeAccessRightProps) =>
    (): void => {
      if (equals(type, ShareType.Contact)) {
        deleteAccessRightContact({ dashboardId, id });
        closeAskRevokeAccessRight();

        return;
      }

      deleteAccessRightContactGroup({ dashboardId, id });
      closeAskRevokeAccessRight();
    };

  return {
    changePage,
    changeSort,
    closeAskRevokeAccessRight,
    confirmRevokeAccessRight,
    formattedRows: formatListingData(rows),
    getRowProperty,
    page,
    resetColumns,
    selectedColumnIds,
    selectedRows,
    setLimit,
    setSelectedColumnIds,
    setSelectedRows,
    sortf,
    sorto
  };
};

export default useListing;

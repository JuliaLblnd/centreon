import { createStore } from 'jotai';

import { Module } from '@centreon/ui';

import { Data, FormThreshold, GlobalRefreshInterval } from '../../models';

import { ValueFormat, TopBottomSettings } from './models';
import TopBottom from './TopBottom';

interface Props {
  globalRefreshInterval: GlobalRefreshInterval;
  isFromPreview?: boolean;
  panelData: Data;
  panelOptions: {
    refreshInterval: 'default' | 'custom';
    refreshIntervalCustom?: number;
    threshold: FormThreshold;
    topBottomSettings: TopBottomSettings;
    valueFormat: ValueFormat;
  };
  refreshCount: number;
  store: ReturnType<typeof createStore>;
}

const Widget = ({
  store,
  globalRefreshInterval,
  panelData,
  panelOptions,
  refreshCount,
  isFromPreview
}: Props): JSX.Element => {
  return (
    <Module maxSnackbars={1} seedName="topbottom" store={store}>
      <TopBottom
        globalRefreshInterval={globalRefreshInterval}
        isFromPreview={isFromPreview}
        metrics={panelData.metrics}
        refreshCount={refreshCount}
        refreshInterval={panelOptions.refreshInterval}
        refreshIntervalCustom={panelOptions.refreshIntervalCustom}
        resources={panelData.resources}
        threshold={panelOptions.threshold}
        topBottomSettings={panelOptions.topBottomSettings}
        valueFormat={panelOptions.valueFormat}
      />
    </Module>
  );
};

export default Widget;

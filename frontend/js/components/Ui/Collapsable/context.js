// @flow
import * as React from 'react';

export type Context = {|
  +visible: boolean,
  +setVisible: (value: boolean | ((updaterValue: boolean) => boolean)) => void,
  +onClose?: () => void | Promise<any>,
  +disabled?: boolean,
|};

export const CollapsableContext = React.createContext<Context>({
  visible: false,
  setVisible: () => undefined,
  onClose: () => undefined,
  disabled: false,
});

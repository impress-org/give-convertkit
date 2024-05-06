/**
 * @unreleased
 */

type windowData = {
  requiresSetup: boolean;
  settingsUrl: string;
  forms: tag[];
  tags: lists[];
};

export type tag = {
  id: string;
  name: string;
};

export type lists = {
  id: string;
  name: string;
};

declare const window: {
  GiveConvertKit: windowData;
} & Window;

export function getWindowData(): windowData {
  return window.GiveConvertKit;
}

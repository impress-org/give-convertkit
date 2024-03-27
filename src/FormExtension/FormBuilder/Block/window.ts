/**
 * @unreleased
 */

type windowData = {
  requiresSetup: boolean;
  settingsUrl: string;
  forms: [];
  tags: [];
};

declare const window: {
  GiveConvertKit: windowData;
} & Window;

export function getWindowData(): windowData {
  return window.GiveConvertKit;
}

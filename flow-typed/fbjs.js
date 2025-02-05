declare module 'JSResourceReference' {
  declare export interface JSResourceReference<T> {
    getModuleId(): string;
    getModuleIfRequired(): ?T;
    load(): Promise<T>;
  }
}
